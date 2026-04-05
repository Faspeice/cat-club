<?php

declare(strict_types=1);

$dsn = getenv('CATCLUB_DB_DSN') ?: 'pgsql:host=db;port=5432;dbname=cat_club';
$user = getenv('CATCLUB_DB_USER') ?: 'postgres';
$pass = getenv('CATCLUB_DB_PASS') ?: 'postgres';

return [
    'db' => [
        'dsn' => $dsn,
        'user' => $user,
        'pass' => $pass,
    ],
    'site' => [
        'name' => 'КэтКлуб',
    ],
];

