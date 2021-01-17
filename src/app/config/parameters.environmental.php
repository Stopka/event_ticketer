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
            'from' => [
                'address' => (string)getenv('MAIL_FROM_ADDRESS'),
                'name' => (bool)getenv('MAIL_FROM_NAME') ? getenv('MAIL_FROM_NAME') : null,
            ],
            'replyTo' => [
                'address' => (bool)getenv('MAIL_REPLY_TO_ADDRESS') ?
                    getenv('MAIL_REPLY_TO_ADDRESS') :
                    null,
                'name' => (bool)getenv('MAIL_REPLY_TO_NAME')
                    ? getenv('MAIL_REPLY_TO_NAME')
                    : null,
            ],
        ],
        'api' => [
            "users" => json_decode(
                (string)getenv('API_USERS'),
                true,
                512,
                JSON_THROW_ON_ERROR
            ),
            "authTokens" => json_decode(
                (string)getenv('API_AUTH_TOKENS'),
                true,
                512,
                JSON_THROW_ON_ERROR
            ),
        ],
        'debugger' => [
            "emails" => json_decode(
                (string)getenv('DEBUGGER_EMAILS'),
                true,
                512,
                JSON_THROW_ON_ERROR
            ),
        ],
    ],
];
