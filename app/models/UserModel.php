<?php

declare(strict_types=1);

final class UserModel
{
    public static function findByNick(PDO $pdo, string $nick): ?array
    {
        $st = $pdo->prepare('SELECT id, nick, contact, password_hash, created_at, updated_at FROM users WHERE nick = :nick');
        $st->execute([':nick' => $nick]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public static function findById(PDO $pdo, int $id): ?array
    {
        $st = $pdo->prepare('SELECT id, nick, contact, created_at, updated_at FROM users WHERE id = :id');
        $st->execute([':id' => $id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public static function create(PDO $pdo, string $nick, string $contact, string $passwordHash): int
    {
        $st = $pdo->prepare('INSERT INTO users (nick, contact, password_hash) VALUES (:nick, :contact, :ph) RETURNING id');
        $st->execute([':nick' => $nick, ':contact' => $contact, ':ph' => $passwordHash]);
        $id = $st->fetchColumn();
        return (int)$id;
    }
}

