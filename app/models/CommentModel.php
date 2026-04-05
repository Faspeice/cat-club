<?php

declare(strict_types=1);

final class CommentModel
{
    public static function create(PDO $pdo, int $postId, int $userId, string $body): int
    {
        $st = $pdo->prepare('INSERT INTO comments (post_id, user_id, body) VALUES (:pid,:uid,:body) RETURNING id');
        $st->execute([':pid' => $postId, ':uid' => $userId, ':body' => $body]);
        return (int)$st->fetchColumn();
    }

    public static function listByPost(PDO $pdo, int $postId): array
    {
        $st = $pdo->prepare('
            SELECT c.id, c.body, c.created_at, u.nick
            FROM comments c
            JOIN users u ON u.id = c.user_id
            WHERE c.post_id = :pid
            ORDER BY c.created_at ASC, c.id ASC
        ');
        $st->execute([':pid' => $postId]);
        return $st->fetchAll();
    }
}

