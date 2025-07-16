<?php

namespace Gravitycar\src\Relationships;

use Doctrine\DBAL\Exception;
use Gravitycar\exceptions\GCException;
use Gravitycar\Gravitons\Graviton;
use Gravitycar\src\Fields\IDField;

class RelationshipBase extends Graviton
{
    protected Graviton $gravitonA;
    protected Graviton $gravitonB;

    public function __construct(string $gravitonAName, string $gravitonBName)
    {
        parent::__construct();

        // Create instances of the gravitons
        $gravitonAClass = "Gravitycar\\Gravitons\\{$gravitonAName}\\{$gravitonAName}";
        $gravitonBClass = "Gravitycar\\Gravitons\\{$gravitonBName}\\{$gravitonBName}";

        $this->gravitonA = new $gravitonAClass();
        $this->gravitonB = new $gravitonBClass();

        $this->table = $this->buildTableName();
        $this->buildRelationshipIDFields();
    }

    protected function buildTableName(): string
    {
        return implode('_', ['rel', $this->gravitonA->getType(), $this->gravitonB->getType()]);
    }

    protected function buildIDAColumnName(): string
    {
        return $this->gravitonA->getType() . '_id_a';
    }

    protected function buildIDBColumnName(): string
    {
        return $this->gravitonB->getType() . '_id_b';
    }

    protected function buildRelationshipIDFields(): void
    {
        $idFieldA = new IDField($this);
        $idFieldA->name = $this->buildIDAColumnName();
        $this->fields[$idFieldA->name] = $idFieldA;

        $idFieldB = new IDField($this);
        $idFieldB->name = $this->buildIDBColumnName();
        $this->fields[$idFieldB->name] = $idFieldB;
    }

    public function getFieldsFilePath(): string
    {
        $fieldsFilePath = "Relationships/{$this->type}/fields.php";
        return file_exists($fieldsFilePath) ? $fieldsFilePath : '';
    }

    public function getOtherSide(Graviton $graviton): Graviton
    {
        if (get_class($graviton) === get_class($this->gravitonA)) {
            return $this->gravitonB;
        }

        if (get_class($graviton) === get_class($this->gravitonB)) {
            return $this->gravitonA;
        }

        throw new GCException("Graviton " . get_class($graviton) . " is not part of this relationship: {$this->table}");
    }

    public function getFieldNameForGraviton(Graviton $graviton): string
    {
        if (get_class($graviton) === get_class($this->gravitonA)) {
            return $this->buildIDAColumnName();
        }

        if (get_class($graviton) === get_class($this->gravitonB)) {
            return $this->buildIDBColumnName();
        }

        throw new GCException("Graviton " . get_class($graviton) . " is not part of this relationship: {$this->table}");
    }

    /**
     * @throws Exception
     * @throws GCException
     */
    public function relate(Graviton $sourceGraviton, Graviton $targetGraviton): bool
    {
        $sourceGravitonFieldName = $this->getFieldNameForGraviton($sourceGraviton);
        $targetGravitonFieldName = $this->getFieldNameForGraviton($targetGraviton);

        $this->set($sourceGravitonFieldName, $sourceGraviton->get('id'));
        $this->set($targetGravitonFieldName, $targetGraviton->get('id'));
        $this->set('date_created', $this->getCurrentDateTime());
        $this->set('date_updated', $this->getCurrentDateTime());

        $queryBuilder = $this->db->getConnection()->createQueryBuilder();
        $queryBuilder->insert($this->table);

        foreach ($this->fields as $fieldName => $field) {
            if ($field->getIsDBField()) {
                $sanitizedValue = $this->db->sanitize($field->get());
                $queryBuilder->setValue($fieldName, $queryBuilder->createNamedParameter($sanitizedValue));
            }
        }

        try {
            $queryBuilder->executeStatement();
            return true;
        } catch (Exception $e) {
            throw new GCException("Failed to create relationship: " . $e->getMessage());
        }
    }

    /**
     * @throws Exception
     * @throws GCException
     */
    public function remove(Graviton $sourceGraviton, Graviton $targetGraviton): bool
    {
        $this->set('deleted', 1);

        $sourceGravitonFieldName = $this->getFieldNameForGraviton($sourceGraviton);
        $targetGravitonFieldName = $this->getFieldNameForGraviton($targetGraviton);

        $queryBuilder = $this->db->getConnection()->createQueryBuilder();
        $queryBuilder->update($this->table)
                     ->set('deleted', $queryBuilder->createNamedParameter(1))
                     ->where($sourceGravitonFieldName . ' = :sourceId')
                     ->andWhere($targetGravitonFieldName . ' = :targetId')
                     ->setParameter('sourceId', $this->db->sanitize($sourceGraviton->get('id')))
                     ->setParameter('targetId', $this->db->sanitize($targetGraviton->get('id')));

        try {
            $queryBuilder->executeStatement();
            return true;
        } catch (Exception $e) {
            throw new GCException("Failed to remove relationship: " . $e->getMessage());
        }
    }

    /**
     * @throws Exception
     * @throws GCException
     */
    public function retrieveRelatedIDs(Graviton $sourceGraviton, int $offset = 0, int $limit = 0): array
    {
        if ($limit === 0) {
            $limit = $this->app->getConfig()->get('graviton_list_limit');
        }

        $targetGraviton = $this->getOtherSide($sourceGraviton);
        $sourceGravitonFieldName = $this->getFieldNameForGraviton($sourceGraviton);
        $targetGravitonFieldName = $this->getFieldNameForGraviton($targetGraviton);

        $queryBuilder = $this->db->getConnection()->createQueryBuilder();
        $queryBuilder->select($targetGravitonFieldName)
                     ->from($this->table)
                     ->where($sourceGravitonFieldName . ' = :sourceId')
                     ->andWhere('deleted = :deleted')
                     ->setParameter('sourceId', $this->db->sanitize($sourceGraviton->get('id')))
                     ->setParameter('deleted', 0)
                     ->setFirstResult($offset)
                     ->setMaxResults($limit);

        try {
            $result = $queryBuilder->executeQuery();
            $ids = [];
            while ($row = $result->fetchAssociative()) {
                $ids[] = $row[$targetGravitonFieldName];
            }
            return $ids;
        } catch (Exception $e) {
            throw new GCException("Failed to retrieve related IDs: " . $e->getMessage());
        }
    }

    /**
     * @throws Exception
     * @throws GCException
     */
    public function retrieveRelatedRecords(Graviton $sourceGraviton, int $offset = 0, int $limit = 0): array
    {
        if ($limit === 0) {
            $limit = $this->app->getConfig()->get('graviton_list_limit');
        }

        $ids = $this->retrieveRelatedIDs($sourceGraviton, $offset, $limit);
        $targetGraviton = $this->getOtherSide($sourceGraviton);
        $targetClass = get_class($targetGraviton);

        $objects = [];
        foreach ($ids as $id) {
            $object = new $targetClass();
            if ($object->retrieve($id)) {
                $objects[] = $object;
            }
        }

        return $objects;
    }
}