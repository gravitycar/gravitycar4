<?php

$fieldsList = [
    'id' => [
        'name' => 'id',
        'type' => 'ID',
        'required' => true,
        'validationRules' => [
            'Required',
            'Id'
        ],
    ],

    'name' => [
        'name' => 'name',
        'label' => 'Name',
        'type' => 'Text',
        'required' => false,
        'maxLength' => 255,
        'validationRules' => [
        ],
    ],

    'date_created' => [
        'name' => 'date_created',
        'label' => 'Date Created',
        'type' => 'DateTime',
        'required' => false,
        'validationRules' => [
            'DateTime'
        ],
    ],

    'date_updated' => [
        'name' => 'date_updated',
        'label' => 'Date Updated',
        'type' => 'DateTime',
        'required' => false,
        'validationRules' => [
            'DateTime'
        ],
    ],

    'created_by_id' => [
        'name' => 'created_by_id',
        'type' => 'RelatedRecord',
        'relatedRecordType' => 'Users',
        'displayField' => 'created_by_name',
        'validationRules' => [
            'Required',
            'Id'
        ],
    ],

    'created_by_name' => [
        'name' => 'created_by_name',
        'label' => 'Created By',
        'type' => 'text',
        'required' => false,
        'isDBField' => false,
        'validationRules' => [
            'Alphanumeric'
        ],
    ],

    'updated_by_id' => [
        'name' => 'updated_by_id',
        'type' => 'RelatedRecord',
        'relatedRecordType' => 'Users',
        'displayField' => 'created_by_name',
        'validationRules' => [
            'Required',
            'Id'
        ],
    ],

    'updated_by_name' => [
        'name' => 'updated_by_name',
        'label' => 'Updated By',
        'type' => 'Text',
        'required' => false,
        'isDBField' => false,
        'validationRules' => [
            'Alphanumeric'
        ],
    ],

    'deleted' => [
        'name' => 'deleted',
        'label' => 'Deleted',
        'type' => 'bool',
        'whitelist' => [0, 1, true, false],
        'validationRules' => [
            'Boolean'
        ],
    ],
];