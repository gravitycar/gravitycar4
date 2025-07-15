<?php
$fieldsList = [

    'username' => [
        'name' => 'username',
        'label' => 'Username',
        'type' => 'Text',
        'required' => true,
        'maxLength' => 50,
        'validationRules' => [
            'Required',
        ],
    ],
    
    'password' => [
        'label' => 'Password',
        'name' => 'password',
        'type' => 'Password',
        'required' => false,
        'maxLength' => 100,
        'validationRules' => [
            'Password'
        ],
    ],

    'user_type' => [
        'label' => 'User Type',
        'name' => 'user_type',
        'type' => 'Enum',
        'defaultValue' => 'regular',
        'optionsClass' => 'Users',
        'optionsMethod' => 'getUserTypes',
        'validationRules' => [
                'Options',
        ],
    ],
];

$indicesList = [
    'idx_username' => [
        'name' => 'idx_username',
        'fields' => ['username', 'deleted'],
        'unique' => true,
    ],
    'idx_id' => [
        'name' => 'idx_id',
        'fields' => ['id', 'deleted'],
        'unique' => false,
    ],
];
