<?php
$fieldsList = [
    'first_name' => [
        'name' => 'first_name',
        'label' => 'First Name',
        'type' => 'Text',
        'required' => false,
        'maxLength' => 50,
        'validationRules' => [
            'LettersOnly'
        ],
    ],

    'last_name' => [
        'name' => 'last_name',
        'label' => 'Last Name',
        'type' => 'Text',
        'required' => false,
        'maxLength' => 100,
        'validationRules' => [
            'Alphanumeric'
        ],
    ],
];