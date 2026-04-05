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

    /**
     * @return array{items:array<int,string>, total:int}
     */
    public static function listNicksPage(PDO $pdo, int $limit, int $offset): array
    {
        $limit = max(1, min(50, $limit));
        $offset = max(0, $offset);
        $total = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        $st = $pdo->prepare('SELECT nick FROM users ORDER BY id DESC LIMIT :lim OFFSET :off');
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->bindValue(':off', $offset, PDO::PARAM_INT);
        $st->execute();
        $items = array_map(static fn($r) => (string)$r['nick'], $st->fetchAll());

        return ['items' => $items, 'total' => $total];
    }
}

