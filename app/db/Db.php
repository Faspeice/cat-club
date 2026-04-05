<?php

declare(strict_types=1);

final class Db
{
    private static ?PDO $pdo = null;

    public static function pdo(array $config): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $db = $config['db'] ?? [];
        $dsn = is_array($db) ? (string)($db['dsn'] ?? '') : '';
        $user = is_array($db) ? (string)($db['user'] ?? '') : '';
        $pass = is_array($db) ? (string)($db['pass'] ?? '') : '';
        if ($dsn === '') {
            throw new RuntimeException('DB is not configured. Copy app/config/config.example.php to app/config/config.php');
        }

        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        self::$pdo = $pdo;
        return $pdo;
    }
}

