<?php
/** @var array<int,array<string,mixed>> $topics */
/** @var int $page */
/** @var int $pages */
declare(strict_types=1);

$query = [];
?>
<header class="page-header">
    <h1 class="page-title">Форум</h1>
    <a class="btn btn-primary btn-sm" href="/" title="Вернуться в ленту">Лента</a>
</header>

<?php if (empty($topics)): ?>
    <div class="card">
        <p>Тем пока нет. После подключения БД здесь появятся темы форума с пагинацией.</p>
        <p><a class="btn btn-sm" href="/forum/1" title="Открыть пример темы">Открыть пример темы</a></p>
    </div>
<?php else: ?>
    <section class="card" aria-label="Темы форума">
        <ul class="sidebar-list">
            <?php foreach ($topics as $t): ?>
                <li>
                    <a href="/forum/<?= (int)$t['id'] ?>" title="Открыть тему: <?= e((string)$t['title']) ?>">
                        <?= e((string)$t['title']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>

    <?php
    $basePath = '/forum';
    require __DIR__ . '/../partials/pagination.php';
    ?>
<?php endif; ?>

