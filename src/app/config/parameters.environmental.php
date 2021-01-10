<?php

declare(strict_types=1);

return [
    'parameters' => [
        'timezone' => getenv('TZ'),
        'format' => [
            'date' => getenv('FORMAT_DATE'),
            'time' => getenv('FORMAT_TIME'),
        ],
        'database' => [
            'host' => getenv('DATABASE_HOST'),
            'dbname' => getenv('DATABASE_NAME'),
            'user' => getenv('DATABASE_USER'),
            'password' => getenv('DATABASE_PASSWORD'),
        ],
        'host' => [
            'name' => getenv('HOST_NAME'),
            'domain' => getenv('HOST_DOMAIN'),
        ],
        'email' => [
            'from' => [getenv('MAIL_FROM'), getenv('MAIL_FROM_NAME')],
            'replyTo' => [getenv('MAIL_REPLY_TO'), getenv('MAIL_REPLY_TO_NAME')],
        ],
        'api' => [
            "users" => json_decode(getenv('API_USERS'), true, 512, JSON_THROW_ON_ERROR),
        ],
    ],
];
