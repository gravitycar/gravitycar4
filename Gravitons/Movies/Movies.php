<?php

namespace Gravitycar\Gravitons\Movies;

use Gravitycar\exceptions\GCException;
use Gravitycar\Gravitons\Graviton;

class Movies extends Graviton
{
    protected string $table = 'movies';
    protected string $type = 'Movies';
    protected string $label = 'Movies';
    protected string $labelSingular = 'Movie';
    protected array $templates = ['base'];


    public function getTitle(): string
    {
        return $this->get('name') ?? '';
    }


    /**
     * @throws GCException
     */
    public function getIMDBData(): ?array
    {
        $title = $this->getTitle();

        if (empty($title)) {
            return null;
        }

        // Using OMDb API (free IMDB data API)
        $apiKey = $this->app->getConfig()->get('open_imdb_api_key');
        if (empty($apiKey)) {
            throw new GCException('OMDB API key not configured');
        }

        $url = 'http://www.omdbapi.com/';
        $params = [
            'apikey' => $apiKey,
            't' => $title,
            'type' => 'movie',
            'plot' => 'full'
        ];

        $queryString = http_build_query($params);
        $fullUrl = $url . '?' . $queryString;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fullUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Gravitycar/1.0');

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || $response === false) {
            return null;
        }

        $data = json_decode($response, true);

        if ($data && $data['Response'] === 'True') {
            return $data;
        }

        return null;
    }
}