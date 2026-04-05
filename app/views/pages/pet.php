<?php
/** @var array<string,mixed> $pet */
declare(strict_types=1);

$img = (string)($pet['photo_url'] ?? '');
$img = $img !== '' ? $img : '/assets/img/placeholder-cat.svg';
$photos = $pet['photos'] ?? [];
if (!is_array($photos)) {
    $photos = [];
}
?>
<div class="pet-main">
    <div class="pet-main-image">
        <img src="<?= e($img) ?>" alt="<?= e((string)($pet['name'] ?? 'Питомец')) ?>" loading="lazy">
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
    <?php if (empty($photos)): ?>
        <p>Пока дополнительных фото нет.</p>
    <?php else: ?>
        <div class="gallery-thumbnails">
            <?php foreach ($photos as $ph): ?>
                <?php $u = is_array($ph) ? (string)($ph['photo_url'] ?? '') : (string)$ph; ?>
                <?php $u = $u !== '' ? $u : '/assets/img/placeholder-cat.svg'; ?>
                <div class="thumbnail">
                    <img src="<?= e($u) ?>" alt="Фото питомца" loading="lazy">
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

