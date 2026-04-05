<?php

declare(strict_types=1);

final class EventModel
{
    /**
     * @return array<int,array<string,mixed>>
     */
    public static function listUpcoming(PDO $pdo, int $limit = 20): array
    {
        $limit = max(1, min(100, $limit));
        $st = $pdo->prepare('SELECT id, title, body, place, starts_at, ends_at, created_at, updated_at
                             FROM events
                             ORDER BY starts_at ASC
                             LIMIT :limit');
        $st->bindValue(':limit', $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    public static function create(PDO $pdo, string $title, string $body, string $place, string $startsAt, ?string $endsAt): int
    {
        $st = $pdo->prepare('INSERT INTO events (title, body, place, starts_at, ends_at) VALUES (:title,:body,:place,:sa,:ea) RETURNING id');
        $st->execute([
            ':title' => $title,
            ':body' => $body,
            ':place' => $place,
            ':sa' => $startsAt,
            ':ea' => $endsAt,
        ]);
        return (int)$st->fetchColumn();
    }

    public static function lastUpdated(PDO $pdo): int
    {
        $ts = $pdo->query('SELECT COALESCE(MAX(EXTRACT(EPOCH FROM updated_at)), 0) FROM events')->fetchColumn();
        return (int)$ts;
    }
}

