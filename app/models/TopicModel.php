<?php

declare(strict_types=1);

final class TopicModel
{
    public static function listTitles(PDO $pdo, int $limit = 20): array
    {
        $limit = max(1, min(100, $limit));
        $st = $pdo->prepare('SELECT title FROM topics ORDER BY updated_at DESC, id DESC LIMIT :limit');
        $st->bindValue(':limit', $limit, PDO::PARAM_INT);
        $st->execute();
        return array_map(static fn($r) => (string)$r['title'], $st->fetchAll());
    }

    /**
     * @return array{items:array<int,array<string,mixed>>, total:int}
     */
    public static function list(PDO $pdo, int $limit, int $offset): array
    {
        $limit = max(1, min(50, $limit));
        $offset = max(0, $offset);

        $total = (int)$pdo->query('SELECT COUNT(*) FROM topics')->fetchColumn();
        $st = $pdo->prepare('SELECT id, title, slug, created_at, updated_at FROM topics ORDER BY updated_at DESC, id DESC LIMIT :limit OFFSET :offset');
        $st->bindValue(':limit', $limit, PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, PDO::PARAM_INT);
        $st->execute();
        return ['items' => $st->fetchAll(), 'total' => $total];
    }

    public static function find(PDO $pdo, int $id): ?array
    {
        $st = $pdo->prepare('SELECT id, title, slug, created_at, updated_at FROM topics WHERE id = :id');
        $st->execute([':id' => $id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public static function lastUpdated(PDO $pdo, ?int $topicId = null): int
    {
        if ($topicId !== null) {
            $st = $pdo->prepare('SELECT COALESCE(EXTRACT(EPOCH FROM updated_at),0) FROM topics WHERE id=:id');
            $st->execute([':id' => $topicId]);
            return (int)$st->fetchColumn();
        }
        $ts = $pdo->query('SELECT COALESCE(MAX(EXTRACT(EPOCH FROM updated_at)), 0) FROM topics')->fetchColumn();
        return (int)$ts;
    }
}

