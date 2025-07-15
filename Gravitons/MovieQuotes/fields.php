<?php
$fieldsList = [
    'name' => [
        'name' => 'name',
        'label' => 'Quote',
        'type' => 'Text',
        'required' => true,
        'maxLength' => 255,
        'validationRules' => [
            'Required',
        ],
    ],

    'movie_id' => [
        'name' => 'movie_id',
        'type' => 'RelatedRecord',
        'relatedRecordType' => 'Movies',
        'displayField' => 'movie_title',
        'validationRules' => [
            'Required',
            'Id'
        ],
    ],

    'movie_title' => [
        'name' => 'movie_title',
        'label' => 'Movie Title',
        'type' => 'Text',
        'required' => false,
        'isDBField' => false,
    ],
];