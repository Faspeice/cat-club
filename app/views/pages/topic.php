<?php
/** @var array<string,mixed> $topic */
/** @var array<int,array<string,mixed>> $posts */
declare(strict_types=1);
?>
<header class="page-header">
    <h1 class="page-title"><?= e((string)($topic['title'] ?? 'Тема')) ?></h1>
    <a class="btn btn-primary btn-sm" href="/forum" title="К списку тем">Все темы</a>
</header>

<?php if (!empty($user['is_auth'])): ?>
    <section class="card" aria-label="Добавить пост" style="margin-bottom:20px">
        <h2 class="sidebar-title" style="margin-bottom:10px">Новый пост</h2>
        <form action="/api/posts/create" method="post">
            <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
            <input type="hidden" name="topic_id" value="<?= (int)($topic['id'] ?? 0) ?>">
            <div class="field" style="margin-bottom:12px">
                <label class="label" for="post-title">Заголовок</label>
                <input class="input" id="post-title" name="title" type="text" required>
            </div>
            <div class="field" style="margin-bottom:12px">
                <label class="label" for="post-body">Текст</label>
                <textarea class="textarea" id="post-body" name="body" required></textarea>
            </div>
            <div class="field" style="margin-bottom:12px">
                <label class="label" for="post-photo">Фото (URL)</label>
                <input class="input" id="post-photo" name="photo_url" type="url" placeholder="https://...">
            </div>
            <button class="btn btn-primary" type="submit" title="Опубликовать">Опубликовать</button>
        </form>
    </section>
<?php endif; ?>

<?php if (empty($posts)): ?>
    <div class="card">
        <p>В этой теме пока нет постов.</p>
        <p>Когда подключим БД и формы, здесь будут записи и комментарии.</p>
    </div>
<?php else: ?>
    <?php foreach ($posts as $post): ?>
        <article class="post-card">
            <h2 class="post-title"><?= e((string)($post['title'] ?? '')) ?></h2>
            <div class="meta-row">
                <div><?= e((string)($post['nick'] ?? '')) ?></div>
                <div>•</div>
                <div><time datetime="<?= e((string)($post['created_at'] ?? '')) ?>"><?= e((string)($post['created_at'] ?? '')) ?></time></div>
            </div>
            <p class="post-text"><?= e((string)($post['body'] ?? '')) ?></p>

            <?php $comments = is_array($post['comments'] ?? null) ? $post['comments'] : []; ?>
            <section aria-label="Комментарии" style="margin-top:14px">
                <h3 style="font-size:14px;margin-bottom:8px">Комментарии</h3>
                <?php if (empty($comments)): ?>
                    <div style="color:var(--muted);font-size:13px">Пока нет комментариев.</div>
                <?php else: ?>
                    <ul style="display:flex;flex-direction:column;gap:8px">
                        <?php foreach ($comments as $c): ?>
                            <li class="card" style="padding:12px">
                                <div class="meta-row">
                                    <div><?= e((string)($c['nick'] ?? '')) ?></div>
                                    <div>•</div>
                                    <div><time datetime="<?= e((string)($c['created_at'] ?? '')) ?>"><?= e((string)($c['created_at'] ?? '')) ?></time></div>
                                </div>
                                <div style="margin-top:6px"><?= e((string)($c['body'] ?? '')) ?></div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if (!empty($user['is_auth'])): ?>
                    <form action="/api/comments/create" method="post" style="margin-top:10px">
                        <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
                        <input type="hidden" name="post_id" value="<?= (int)($post['id'] ?? 0) ?>">
                        <div class="field" style="margin-bottom:10px">
                            <label class="label" for="c-<?= (int)($post['id'] ?? 0) ?>">Добавить комментарий</label>
                            <textarea class="textarea" id="c-<?= (int)($post['id'] ?? 0) ?>" name="body" required></textarea>
                        </div>
                        <button class="btn btn-primary btn-sm" type="submit" title="Отправить комментарий">Отправить</button>
                    </form>
                <?php else: ?>
                    <div style="margin-top:10px;font-size:13px">
                        <a href="/login" title="Войти, чтобы комментировать">Войдите</a>, чтобы оставить комментарий.
                    </div>
                <?php endif; ?>
            </section>
        </article>
    <?php endforeach; ?>
<?php endif; ?>

