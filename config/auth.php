<?php

return [
    'defaults' => [
        'guard' => 'api',
        'passwords' => 'users',
    ],

    'guards' => [
        'api' => [
            'driver' => 'jwt',
            'provider' => 'users',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => \App\Models\User::class
        ]
    ]
]

// mobile app 
// langkah2 membuat mobile
// developer -> cs/admin
// alur kerja/flowchart
// flowchart
//implementasi codingan -> vscode
// testing
// flutter


?>