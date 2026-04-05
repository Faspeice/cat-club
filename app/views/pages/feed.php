<?php
/** @var array<int,array<string,string>> $posts */
/** @var array<int,string> $topics */
/** @var array<int,string> $members */
/** @var string $selectedTopic */
/** @var string $selectedAuthor */
declare(strict_types=1);

$selectedTopic = $selectedTopic ?? '';
$selectedAuthor = $selectedAuthor ?? '';
?>
<div class="layout-grid">
    <div>
        <div class="page-header">
            <h1 class="page-title">Лента активностей</h1>
            <?php if ($selectedTopic !== '' || $selectedAuthor !== ''): ?>
                <a class="btn btn-primary btn-sm" href="/" title="Сбросить фильтры">Сбросить фильтры</a>
            <?php endif; ?>
        </div>

        <?php foreach ($posts as $post): ?>
            <article class="post-card">
                <div class="post-header">
                    <div class="user-info">
                        <div class="avatar" aria-hidden="true"></div>
                        <div class="username-block">
                            <div><?= e((string)$post['user']) ?></div>
                            <div><time datetime="<?= e((string)$post['date']) ?>"><?= e((string)$post['date']) ?></time></div>
                        </div>
                    </div>
                    <span class="tag"><?= e((string)$post['tag']) ?></span>
                </div>

                <h2 class="post-title"><?= e((string)$post['title']) ?></h2>

                <img class="post-image"
                     src="<?= e((string)($post['image'] ?: '/assets/img/placeholder-cat.svg')) ?>"
                     alt="<?= e((string)$post['image_alt']) ?>"
                     loading="lazy">

                <p class="post-text"><?= e((string)$post['text']) ?></p>
                <div class="post-footer">
                    <button class="icon-btn" type="button" title="Поставить лайк" aria-label="Лайк">♡</button>
                    <button class="icon-btn" type="button" title="Комментарии (в разработке)" aria-label="Комментарии">💬</button>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <aside>
        <section class="sidebar-section" aria-label="Темы">
            <h2 class="sidebar-title">Темы</h2>
            <div class="sidebar-box">
                <ul class="list-content sidebar-list">
                    <?php foreach ($topics as $t): ?>
                        <li>
                            <a href="/?topic=<?= urlencode($t) ?>" title="Показать посты по теме: <?= e($t) ?>">
                                <?= e($t) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="sidebar-nav" aria-hidden="true"><span>◀</span><span>▶</span></div>
            </div>
        </section>

        <section class="sidebar-section" aria-label="Участники">
            <h2 class="sidebar-title">Участники</h2>
            <div class="sidebar-box">
                <ul class="list-content sidebar-list">
                    <?php foreach ($members as $m): ?>
                        <li>
                            <a href="/?author=<?= urlencode($m) ?>" title="Показать посты участника: <?= e($m) ?>">
                                <?= e($m) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="sidebar-nav" aria-hidden="true"><span>◀</span><span>▶</span></div>
            </div>
        </section>
    </aside>
</div>

