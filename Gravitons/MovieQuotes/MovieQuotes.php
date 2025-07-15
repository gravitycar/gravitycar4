<?php

namespace Gravitycar\Gravitons\MovieQuotes;

use Gravitycar\Gravitons\Graviton;

class MovieQuotes extends Graviton
{
    protected string $table = 'movie_quotes';
    protected string $type = 'MovieQuotes';
    protected string $label = 'Movie Quotes';
    protected string $labelSingular = 'Movie Quote';
    protected array $templates = ['base'];

    public function getQuote(): string
    {
        return $this->get('name') ?? '';
    }
}