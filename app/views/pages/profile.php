<?php
/** @var array<string,mixed> $user */
declare(strict_types=1);
?>
<header class="page-header">
    <h1 class="page-title">Профиль</h1>
    <a class="btn btn-primary btn-sm" href="/pets" title="Перейти к галерее питомцев">Питомцы</a>
</header>

<section class="card" aria-label="Данные владельца">
    <h2 class="sidebar-title" style="margin-bottom:10px">Владелец</h2>
    <div class="pet-details" style="font-size:16px">
        <div><strong>Ник:</strong> <?= e((string)($user['nick'] ?? '')) ?></div>
        <div><strong>Контакты:</strong> <?= e((string)($user['contact'] ?? '')) ?></div>
    </div>
    <p style="margin-top:12px;color:var(--muted)">Дальше здесь будет список ваших питомцев и формы добавления.</p>
</section>

