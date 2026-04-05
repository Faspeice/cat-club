<?php
/** @var array<int,array<string,mixed>> $pets */
/** @var array<int,string> $breeds */
/** @var string $selectedBreed */
/** @var int $page */
/** @var int $pages */
declare(strict_types=1);

$query = [];
if ($selectedBreed !== '') {
    $query['breed'] = $selectedBreed;
}
?>
<header class="page-header">
    <h1 class="page-title">Галерея питомцев</h1>
</header>

<?php if (!empty($user['is_auth'])): ?>
    <section class="card" aria-label="Добавить питомца" style="margin-bottom:20px">
        <h2 class="sidebar-title" style="margin-bottom:10px">Добавить питомца</h2>
        <form action="/api/pets/create" method="post">
            <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
            <div class="filter-row">
                <div class="field" style="flex:1;min-width:220px">
                    <label class="label" for="pet-name">Кличка</label>
                    <input class="input" id="pet-name" name="name" type="text" required>
                </div>
                <div class="field" style="flex:1;min-width:220px">
                    <label class="label" for="pet-breed">Порода</label>
                    <input class="input" id="pet-breed" name="breed" type="text" required>
                </div>
                <div class="field" style="width:140px">
                    <label class="label" for="pet-age">Возраст</label>
                    <input class="input" id="pet-age" name="age" type="number" min="0" max="50" value="0" required>
                </div>
            </div>
            <div class="filter-row">
                <div class="field" style="flex:1;min-width:260px">
                    <label class="label" for="pet-photo">Фото (URL)</label>
                    <input class="input" id="pet-photo" name="photo_url" type="url" placeholder="https://..." >
                </div>
            </div>
            <div class="field" style="margin-bottom:12px">
                <label class="label" for="pet-story">История</label>
                <textarea class="textarea" id="pet-story" name="story"></textarea>
            </div>
            <button class="btn btn-primary" type="submit" title="Сохранить питомца">Сохранить</button>
        </form>
    </section>
<?php endif; ?>

<form class="filter-row" action="/pets" method="get">
    <div class="field">
        <label class="label" for="breed">Порода</label>
        <select class="select" id="breed" name="breed" title="Фильтр по породе">
            <option value="">Все породы</option>
            <?php foreach ($breeds as $b): ?>
                <option value="<?= e($b) ?>" <?= $selectedBreed === $b ? 'selected' : '' ?>><?= e($b) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button class="btn btn-primary" type="submit" title="Применить фильтр">Показать</button>
</form>

<?php if (empty($pets)): ?>
    <div class="card">
        <p>Пока питомцев нет. После подключения БД здесь появятся карточки с реальными данными.</p>
        <p><a class="btn btn-sm" href="/pets/1" title="Пример карточки питомца">Открыть пример карточки</a></p>
    </div>
<?php else: ?>
    <div class="gallery-grid" role="list">
        <?php foreach ($pets as $pet): ?>
            <?php
            $img = (string)($pet['photo_url'] ?? '');
            $img = $img !== '' ? $img : '/assets/img/placeholder-cat.svg';
            ?>
            <a class="pet-card" role="listitem" href="/pets/<?= (int)$pet['id'] ?>" title="Открыть карточку питомца">
                <div class="pet-image-container">
                    <img class="pet-image" src="<?= e($img) ?>" alt="<?= e((string)($pet['breed'] ?? 'Питомец')) ?>" loading="lazy">
                </div>
                <div class="pet-info">
                    <span class="pet-name">Кличка: <?= e((string)($pet['name'] ?? '')) ?></span>
                    <div class="pet-detail">Возраст: <?= e((string)($pet['age'] ?? '')) ?></div>
                    <div class="pet-detail">Порода: <?= e((string)($pet['breed'] ?? '')) ?></div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <?php
    $basePath = '/pets';
    require __DIR__ . '/../partials/pagination.php';
    ?>
<?php endif; ?>

