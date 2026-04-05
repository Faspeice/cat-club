<?php

declare(strict_types=1);

final class PostModel
{
    /**
     * Feed list with optional filters by topic title and author nick.
     *
     * @return array{items:array<int,array<string,mixed>>, total:int}
     */
    public static function listFeed(PDO $pdo, ?string $topicTitle, ?string $authorNick, int $limit, int $offset): array
    {
        $limit = max(1, min(50, $limit));
        $offset = max(0, $offset);

        $where = [];
        $params = [];
        if (is_string($topicTitle) && $topicTitle !== '') {
            $where[] = 't.title = :topic';
            $params[':topic'] = $topicTitle;
        }
        if (is_string($authorNick) && $authorNick !== '') {
            $where[] = 'u.nick = :nick';
            $params[':nick'] = $authorNick;
        }
        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        $countSql = 'SELECT COUNT(*) FROM posts p JOIN users u ON u.id=p.user_id LEFT JOIN topics t ON t.id=p.topic_id ' . $whereSql;
        $countSt = $pdo->prepare($countSql);
        $countSt->execute($params);
        $total = (int)$countSt->fetchColumn();

        $sql = '
            SELECT p.id, p.title, p.body, p.photo_url, p.created_at, p.updated_at,
                   u.nick,
                   COALESCE(t.title, \'\') AS topic_title
            FROM posts p
            JOIN users u ON u.id = p.user_id
            LEFT JOIN topics t ON t.id = p.topic_id
            ' . $whereSql . '
            ORDER BY p.created_at DESC, p.id DESC
            LIMIT :limit OFFSET :offset
        ';
        $st = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $st->bindValue($k, $v, PDO::PARAM_STR);
        }
        $st->bindValue(':limit', $limit, PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, PDO::PARAM_INT);
        $st->execute();
        return ['items' => $st->fetchAll(), 'total' => $total];
    }
    /**
     * @return array{items:array<int,array<string,mixed>>, total:int}
     */
    public static function list(PDO $pdo, ?int $topicId, int $limit, int $offset): array
    {
        $limit = max(1, min(50, $limit));
        $offset = max(0, $offset);

        if ($topicId !== null) {
            $countSt = $pdo->prepare('SELECT COUNT(*) FROM posts WHERE topic_id = :tid');
            $countSt->execute([':tid' => $topicId]);
            $total = (int)$countSt->fetchColumn();

            $st = $pdo->prepare('
                SELECT p.id, p.title, p.body, p.photo_url, p.created_at, p.updated_at,
                       u.nick
                FROM posts p
                JOIN users u ON u.id = p.user_id
                WHERE p.topic_id = :tid
                ORDER BY p.created_at DESC, p.id DESC
                LIMIT :limit OFFSET :offset
            ');
            $st->bindValue(':tid', $topicId, PDO::PARAM_INT);
            $st->bindValue(':limit', $limit, PDO::PARAM_INT);
            $st->bindValue(':offset', $offset, PDO::PARAM_INT);
            $st->execute();
            return ['items' => $st->fetchAll(), 'total' => $total];
        }

        $total = (int)$pdo->query('SELECT COUNT(*) FROM posts')->fetchColumn();
        $st = $pdo->prepare('
            SELECT p.id, p.title, p.body, p.photo_url, p.created_at, p.updated_at,
                   u.nick,
                   COALESCE(t.title, \'\') AS topic_title
            FROM posts p
            JOIN users u ON u.id = p.user_id
            LEFT JOIN topics t ON t.id = p.topic_id
            ORDER BY p.created_at DESC, p.id DESC
            LIMIT :limit OFFSET :offset
        ');
        $st->bindValue(':limit', $limit, PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, PDO::PARAM_INT);
        $st->execute();
        return ['items' => $st->fetchAll(), 'total' => $total];
    }

    public static function create(PDO $pdo, int $userId, ?int $topicId, string $title, string $body, string $photoUrl): int
    {
        $st = $pdo->prepare('INSERT INTO posts (user_id, topic_id, title, body, photo_url) VALUES (:uid,:tid,:title,:body,:photo) RETURNING id');
        $st->execute([
            ':uid' => $userId,
            ':tid' => $topicId,
            ':title' => $title,
            ':body' => $body,
            ':photo' => $photoUrl,
        ]);
        return (int)$st->fetchColumn();
    }

    public static function lastUpdated(PDO $pdo, ?int $topicId = null): int
    {
        if ($topicId !== null) {
            $st = $pdo->prepare('SELECT COALESCE(MAX(EXTRACT(EPOCH FROM updated_at)), 0) FROM posts WHERE topic_id=:tid');
            $st->execute([':tid' => $topicId]);
            return (int)$st->fetchColumn();
        }
        $ts = $pdo->query('SELECT COALESCE(MAX(EXTRACT(EPOCH FROM updated_at)), 0) FROM posts')->fetchColumn();
        return (int)$ts;
    }

    public static function exists(PDO $pdo, int $postId): bool
    {
        if ($postId < 1) {
            return false;
        }
        $st = $pdo->prepare('SELECT 1 FROM posts WHERE id = :id');
        $st->execute([':id' => $postId]);
        return (bool)$st->fetchColumn();
    }

    /** @return bool true if the post is liked after this call, false if unliked */
    public static function toggleLike(PDO $pdo, int $postId, int $userId): bool
    {
        $del = $pdo->prepare('DELETE FROM post_likes WHERE post_id = :p AND user_id = :u');
        $del->execute([':p' => $postId, ':u' => $userId]);
        if ($del->rowCount() > 0) {
            return false;
        }
        $ins = $pdo->prepare('INSERT INTO post_likes (post_id, user_id) VALUES (:p, :u)');
        $ins->execute([':p' => $postId, ':u' => $userId]);

        return true;
    }

    /**
     * @param list<int> $postIds
     * @return array{counts:array<int,int>, liked:array<int,bool>}
     */
    public static function feedLikeData(PDO $pdo, array $postIds, ?int $userId): array
    {
        $ids = array_values(array_unique(array_filter(array_map(static fn($x) => (int)$x, $postIds), static fn($x) => $x > 0)));
        $counts = [];
        $liked = [];
        foreach ($ids as $id) {
            $counts[$id] = 0;
            $liked[$id] = false;
        }
        if ($ids === []) {
            return ['counts' => $counts, 'liked' => $liked];
        }
        $in = implode(',', $ids);
        $sql = 'SELECT post_id, COUNT(*)::int AS c FROM post_likes WHERE post_id IN (' . $in . ') GROUP BY post_id';
        foreach ($pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $pid = (int)$row['post_id'];
            $counts[$pid] = (int)$row['c'];
        }
        if ($userId !== null && $userId > 0) {
            $st = $pdo->prepare('SELECT post_id FROM post_likes WHERE user_id = :u AND post_id IN (' . $in . ')');
            $st->execute([':u' => $userId]);
            while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                $liked[(int)$row['post_id']] = true;
            }
        }

        return ['counts' => $counts, 'liked' => $liked];
    }
}

