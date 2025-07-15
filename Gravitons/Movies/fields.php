<?php
$fieldsList = [

    'name' => [
        'name' => 'name',
        'label' => 'Title',
        'type' => 'Text',
        'required' => true,
        'maxLength' => 255,
        'validationRules' => [
            'Required',
        ],
    ],

    'synopsis' => [
        'name' => 'synopsis',
        'label' => 'Synopsis',
        'type' => 'BigText',
        'required' => false,
        'validationRules' => [],
    ],

    'imdb_url' => [
        'name' => 'imdb_url',
        'label' => 'IMDB Link',
        'type' => 'URL',
        'required' => false,
        'maxLength' => 255,
        'validationRules' => [
            'Url',
        ],
    ],

    'movie_poster' => [
        'name' => 'movie_poster',
        'label' => 'Movie Poster',
        'type' => 'Image',
        'required' => false,
    ],
];