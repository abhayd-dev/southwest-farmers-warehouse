<?php

return [

    'defaults' => [
        'guard' => 'warehouse',
        'passwords' => 'warehouse_users',
    ],

    'guards' => [
        'warehouse' => [
            'driver' => 'session',
            'provider' => 'warehouse_users',
        ],
    ],

    'providers' => [
        'warehouse_users' => [
            'driver' => 'eloquent',
            'model' => App\Models\WareUser::class,
        ],
    ],

    'passwords' => [
        'warehouse_users' => [
            'provider' => 'warehouse_users',
            'table' => 'ware_password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,
];
