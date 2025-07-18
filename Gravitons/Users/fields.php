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
        'optionsClass' => '\Gravitycar\Gravitons\Users\Users',
        'optionsMethod' => 'getUserTypes',
        'validationRules' => [
                'Options',
        ],
    ],

    'email' => [
        'name' => 'email',
        'label' => 'Email',
        'type' => 'Email',
        'required' => false,
        'maxLength' => 100,
        'validationRules' => [
            'Email',
        ],
    ],

    'last_login' => [
        'name' => 'last_login',
        'label' => 'Last Login',
        'type' => 'DateTime',
        'required' => false,
        'readOnly' => true,
        'validationRules' => [
            'DateTime',
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
