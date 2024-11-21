<?php

return [
    'allowed_classes' => [
        App\Services\ExampleService::class => [
            'retries' => 5,
            'delay' => 5,
            'priority' => 3,
        ],
        App\Services\SupportEmailService::class => [
            'retries' => 3,
            'delay' => 3,
            'priority' => 2,
        ],
    ],
    'default_retries' => 0,
    'default_delay' => 0,
    'default_priority' => 3,
    'log_retention_days' => 14,
];