<?php

declare(strict_types=1);

final class PetModel
{
    /**
     * @return array{items:array<int,array<string,mixed>>, total:int}
     */
    public static function list(PDO $pdo, string $breed, int $limit, int $offset): array
    {
        $limit = max(1, min(50, $limit));
        $offset = max(0, $offset);

        if ($breed !== '') {
            $countSt = $pdo->prepare('SELECT COUNT(*) FROM pets WHERE breed = :breed');
            $countSt->execute([':breed' => $breed]);
            $total = (int)$countSt->fetchColumn();

            $st = $pdo->prepare('SELECT id, owner_id, name, breed, age, photo_url, story, created_at, updated_at
                                 FROM pets
                                 WHERE breed = :breed
                                 ORDER BY updated_at DESC, id DESC
                                 LIMIT :limit OFFSET :offset');
            $st->bindValue(':breed', $breed, PDO::PARAM_STR);
            $st->bindValue(':limit', $limit, PDO::PARAM_INT);
            $st->bindValue(':offset', $offset, PDO::PARAM_INT);
            $st->execute();
            $items = $st->fetchAll();
            return ['items' => $items, 'total' => $total];
        }

        $total = (int)$pdo->query('SELECT COUNT(*) FROM pets')->fetchColumn();
        $st = $pdo->prepare('SELECT id, owner_id, name, breed, age, photo_url, story, created_at, updated_at
                             FROM pets
                             ORDER BY updated_at DESC, id DESC
                             LIMIT :limit OFFSET :offset');
        $st->bindValue(':limit', $limit, PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, PDO::PARAM_INT);
        $st->execute();
        $items = $st->fetchAll();
        return ['items' => $items, 'total' => $total];
    }

    public static function listBreeds(PDO $pdo): array
    {
        $st = $pdo->query('SELECT DISTINCT breed FROM pets ORDER BY breed ASC');
        return array_map(static fn($r) => (string)$r['breed'], $st->fetchAll());
    }

    public static function find(PDO $pdo, int $id): ?array
    {
        $st = $pdo->prepare('
            SELECT p.id, p.owner_id, p.name, p.breed, p.age, p.photo_url, p.story, p.created_at, p.updated_at,
                   u.nick AS owner_nick, u.contact AS owner_contact
            FROM pets p
            JOIN users u ON u.id = p.owner_id
            WHERE p.id = :id
        ');
        $st->execute([':id' => $id]);
        $pet = $st->fetch();
        if (!$pet) {
            return null;
        }
        $ph = $pdo->prepare('SELECT photo_url, created_at FROM pet_photos WHERE pet_id = :id ORDER BY id DESC');
        $ph->execute([':id' => $id]);
        $pet['photos'] = $ph->fetchAll();
        return $pet;
    }

    public static function create(PDO $pdo, int $ownerId, string $name, string $breed, int $age, string $photoUrl, string $story): int
    {
        $st = $pdo->prepare('INSERT INTO pets (owner_id, name, breed, age, photo_url, story) VALUES (:oid,:name,:breed,:age,:photo,:story) RETURNING id');
        $st->execute([
            ':oid' => $ownerId,
            ':name' => $name,
            ':breed' => $breed,
            ':age' => $age,
            ':photo' => $photoUrl,
            ':story' => $story,
        ]);
        return (int)$st->fetchColumn();
    }

    public static function addPhoto(PDO $pdo, int $petId, string $photoUrl): void
    {
        if ($photoUrl === '') {
            return;
        }
        $st = $pdo->prepare('INSERT INTO pet_photos (pet_id, photo_url) VALUES (:pid, :url)');
        $st->execute([':pid' => $petId, ':url' => $photoUrl]);
    }

    public static function lastUpdated(PDO $pdo, ?int $petId = null): int
    {
        if ($petId !== null) {
            $st = $pdo->prepare('SELECT GREATEST(COALESCE(EXTRACT(EPOCH FROM updated_at),0), COALESCE((SELECT MAX(EXTRACT(EPOCH FROM created_at)) FROM pet_photos WHERE pet_id=:id),0)) AS ts FROM pets WHERE id=:id');
            $st->execute([':id' => $petId]);
            $ts = $st->fetchColumn();
            return (int)$ts;
        }
        $ts = $pdo->query('SELECT COALESCE(MAX(EXTRACT(EPOCH FROM updated_at)), 0) FROM pets')->fetchColumn();
        return (int)$ts;
    }
}

