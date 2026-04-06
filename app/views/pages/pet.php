<?php
/** @var array<string,mixed> $pet */
declare(strict_types=1);

$img = (string)($pet['photo_url'] ?? '');
$img = $img !== '' ? $img : '/assets/img/placeholder-cat.svg';
$photos = $pet['photos'] ?? [];
if (!is_array($photos)) {
    $photos = [];
}
$isOwner = !empty($user['is_auth']) && (int)($user['id'] ?? 0) === (int)($pet['owner_id'] ?? 0);
?>
<div class="pet-main">
    <div class="pet-main-image">
        <img src="<?= e($img) ?>" alt="<?= e((string)($pet['name'] ?? 'Питомец')) ?>" onerror="this.onerror=null;this.src='/assets/img/placeholder.jpg';" loading="lazy">
    </div>
    <div>
        <h1 class="pet-name-large"><?= e((string)($pet['name'] ?? '')) ?></h1>
        <div class="pet-details">
            <div><strong>Возраст:</strong> <?= e((string)($pet['age'] ?? '')) ?></div>
            <div><strong>Порода:</strong> <?= e((string)($pet['breed'] ?? '')) ?></div>
            <div><strong>Владелец:</strong> <?= e((string)($pet['owner_nick'] ?? '')) ?></div>
            <div><strong>Контакты:</strong> <?= e((string)($pet['owner_contact'] ?? '')) ?></div>
        </div>
    </div>
</div>

<section class="pet-description" aria-label="История питомца">
    <p><?= e((string)($pet['story'] ?? '')) ?></p>
</section>

<section class="card" aria-label="Галерея питомца" style="margin-top:20px">
    <h2 class="sidebar-title" style="margin-bottom:12px">Дополнительные фотографии</h2>
    <?php if ($isOwner): ?>
        <form action="/api/pets/add-photos" method="post" enctype="multipart/form-data" style="margin-bottom:12px">
            <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
            <input type="hidden" name="pet_id" value="<?= (int)($pet['id'] ?? 0) ?>">
            <div class="field" style="margin-bottom:10px">
                <label class="label" for="pet-gallery-files">Загрузить фото в галерею</label>
                <input class="input" id="pet-gallery-files" name="more_photo_files[]" type="file" accept="image/jpeg,image/png,image/webp,image/gif" multiple required>
            </div>
            <button class="btn btn-primary btn-sm" type="submit">Добавить фото</button>
        </form>
    <?php endif; ?>
    <?php if (empty($photos)): ?>
        <p>Пока дополнительных фото нет.</p>
    <?php else: ?>
        <div class="gallery-thumbnails">
            <?php foreach ($photos as $ph): ?>
                <?php $u = is_array($ph) ? (string)($ph['photo_url'] ?? '') : (string)$ph; ?>
                <?php $u = $u !== '' ? $u : '/assets/img/placeholder-cat.svg'; ?>
                <div class="thumbnail">
                    <img src="<?= e($u) ?>" alt="Фото питомца" onerror="this.onerror=null;this.src='/assets/img/placeholder-cat.svg';" loading="lazy">
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

