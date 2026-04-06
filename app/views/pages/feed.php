<?php
/** @var array<int,array<string,mixed>> $posts */
/** @var array<int,string> $topics */
/** @var array<int,string> $members */
/** @var array<int,string> $topicOptions */
/** @var string $selectedTopic */
/** @var string $selectedAuthor */
/** @var int $topicPage */
/** @var int $topicsPages */
/** @var int $memberPage */
/** @var int $membersPages */
/** @var string $feedRedirect */
/** @var bool $dbConnected */
/** @var array<string,mixed> $user */
declare(strict_types=1);

$selectedTopic = $selectedTopic ?? '';
$selectedAuthor = $selectedAuthor ?? '';
$topicPage = max(1, (int)($topicPage ?? 1));
$topicsPages = max(1, (int)($topicsPages ?? 1));
$memberPage = max(1, (int)($memberPage ?? 1));
$membersPages = max(1, (int)($membersPages ?? 1));
$feedRedirect = $feedRedirect ?? '/';
$topicOptions = $topicOptions ?? [];
$dbConnected = !empty($dbConnected);

$prevTopicsUrl = feedSidebarUrl($selectedTopic, $selectedAuthor, max(1, $topicPage - 1), $memberPage);
$nextTopicsUrl = feedSidebarUrl($selectedTopic, $selectedAuthor, min($topicsPages, $topicPage + 1), $memberPage);
$prevMembersUrl = feedSidebarUrl($selectedTopic, $selectedAuthor, $topicPage, max(1, $memberPage - 1));
$nextMembersUrl = feedSidebarUrl($selectedTopic, $selectedAuthor, $topicPage, min($membersPages, $memberPage + 1));
?>
<div class="layout-grid">
    <div>
        <div class="page-header">
            <h1 class="page-title">Лента активностей</h1>
            <div class="page-header-actions">
                <?php if (!empty($user['is_auth']) && $dbConnected): ?>
                    <button type="button" class="btn btn-primary btn-sm" data-open-panel="add-post" aria-expanded="false" aria-controls="add-post" title="Открыть форму нового поста">Добавить пост</button>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($user['is_auth']) && $dbConnected): ?>
            <section class="card feed-add-post" id="add-post" hidden aria-label="Новый пост">
                <h2 class="sidebar-title" style="margin-bottom:12px;font-size:18px">Новый пост</h2>
                <form action="/api/posts/create" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
                    <input type="hidden" name="redirect" value="<?= e($feedRedirect) ?>">
                    <div class="filter-row">
                        <div class="field" style="flex:1;min-width:200px">
                            <label class="label" for="post-title">Заголовок</label>
                            <input class="input" id="post-title" name="title" type="text" required minlength="3" maxlength="200" placeholder="О чём пост?">
                        </div>
                        <?php if ($topicOptions !== []): ?>
                            <div class="field" style="flex:1;min-width:180px">
                                <label class="label" for="post-topic">Тема</label>
                                <select class="input" id="post-topic" name="topic_title" title="Тема поста">
                                    <option value="">Без темы</option>
                                    <?php foreach ($topicOptions as $opt): ?>
                                        <option value="<?= e($opt) ?>"><?= e($opt) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="field" style="margin-bottom:12px">
                        <label class="label" for="post-body">Текст</label>
                        <textarea class="textarea" id="post-body" name="body" required placeholder="Текст поста…"></textarea>
                    </div>
                    <div class="field" style="margin-bottom:12px">
                        <label class="label" for="post-photo-file">Фото (файл, необязательно)</label>
                        <input class="input" id="post-photo-file" name="photo_file" type="file" accept="image/jpeg,image/png,image/webp,image/gif">
                    </div>
                    <button class="btn btn-primary" type="submit">Опубликовать</button>
                </form>
            </section>
        <?php endif; ?>

        <?php foreach ($posts as $post): ?>
            <?php
            $postId = (int)($post['id'] ?? 0);
            $likeCount = (int)($post['like_count'] ?? 0);
            $liked = !empty($post['liked']);
            $comments = $post['comments'] ?? [];
            if (!is_array($comments)) {
                $comments = [];
            }
            $commentCount = count($comments);
            ?>
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
                     onerror="this.onerror=null;this.src='/assets/img/placeholder.jpg';"
                     loading="lazy">

                <p class="post-text"><?= e((string)$post['text']) ?></p>
                <div class="post-footer">
                    <?php if (!empty($user['is_auth']) && $postId > 0): ?>
                        <form class="inline-form" method="post" action="/api/posts/toggle-like">
                            <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
                            <input type="hidden" name="post_id" value="<?= $postId ?>">
                            <input type="hidden" name="redirect" value="<?= e($feedRedirect) ?>">
                            <button class="icon-btn<?= $liked ? ' is-liked' : '' ?>" type="submit" title="Нравится" aria-label="<?= $liked ? 'Убрать лайк' : 'Нравится' ?>"><?= $liked ? '♥' : '♡' ?></button>
                            <span class="like-count"><?= $likeCount ?></span>
                        </form>
                    <?php else: ?>
                        <span class="icon-btn" title="<?= $postId > 0 ? 'Войдите, чтобы ставить лайки' : '' ?>" <?= $postId > 0 ? 'style="cursor:default;opacity:.75"' : '' ?> aria-hidden="true">♡</span>
                        <span class="like-count" aria-label="Число лайков"><?= $likeCount ?></span>
                    <?php endif; ?>
                    <span class="meta-row" aria-label="Комментарии">💬 <?= $commentCount ?></span>
                </div>

                <?php if ($postId > 0): ?>
                    <section class="post-comments" aria-label="Комментарии к посту">
                        <div class="post-comments-title">Комментарии (<?= $commentCount ?>)</div>
                        <?php if ($comments !== []): ?>
                            <ul class="comment-list">
                                <?php foreach ($comments as $c): ?>
                                    <li>
                                        <div><?= e((string)($c['body'] ?? '')) ?></div>
                                        <div class="comment-meta"><?= e((string)($c['nick'] ?? '')) ?> · <time datetime="<?= e((string)($c['created_at'] ?? '')) ?>"><?= e((string)($c['created_at'] ?? '')) ?></time></div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <?php if (!empty($user['is_auth'])): ?>
                            <form class="comment-form" method="post" action="/api/posts/comment">
                                <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
                                <input type="hidden" name="post_id" value="<?= $postId ?>">
                                <input type="hidden" name="redirect" value="<?= e($feedRedirect) ?>">
                                <label class="label" for="comment-<?= $postId ?>">Комментарий</label>
                                <textarea class="textarea" id="comment-<?= $postId ?>" name="body" rows="3" maxlength="2000" required placeholder="Напишите комментарий…"></textarea>
                                <button class="btn btn-primary btn-sm" type="submit">Отправить</button>
                            </form>
                        <?php else: ?>
                            <p class="meta-row"><a href="/login">Войдите</a>, чтобы оставить комментарий.</p>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>
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
                            <a href="<?= e(feedSidebarUrl($t, '', 1, $memberPage)) ?>" title="Показать посты по теме: <?= e($t) ?>">
                                <?= e($t) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <nav class="sidebar-nav" aria-label="Страницы списка тем">
                    <?php if ($topicPage <= 1): ?>
                        <span class="is-disabled" aria-hidden="true">◀</span>
                    <?php else: ?>
                        <a href="<?= e($prevTopicsUrl) ?>" title="Предыдущая страница тем">◀</a>
                    <?php endif; ?>
                    <?php if ($topicPage >= $topicsPages): ?>
                        <span class="is-disabled" aria-hidden="true">▶</span>
                    <?php else: ?>
                        <a href="<?= e($nextTopicsUrl) ?>" title="Следующая страница тем">▶</a>
                    <?php endif; ?>
                </nav>
            </div>
        </section>

        <section class="sidebar-section" aria-label="Участники">
            <h2 class="sidebar-title">Участники</h2>
            <div class="sidebar-box">
                <ul class="list-content sidebar-list">
                    <?php foreach ($members as $m): ?>
                        <li>
                            <a href="<?= e(feedSidebarUrl('', $m, 1, 1)) ?>" title="Показать посты участника: <?= e($m) ?>">
                                <?= e($m) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <nav class="sidebar-nav" aria-label="Страницы списка участников">
                    <?php if ($memberPage <= 1): ?>
                        <span class="is-disabled" aria-hidden="true">◀</span>
                    <?php else: ?>
                        <a href="<?= e($prevMembersUrl) ?>" title="Предыдущая страница участников">◀</a>
                    <?php endif; ?>
                    <?php if ($memberPage >= $membersPages): ?>
                        <span class="is-disabled" aria-hidden="true">▶</span>
                    <?php else: ?>
                        <a href="<?= e($nextMembersUrl) ?>" title="Следующая страница участников">▶</a>
                    <?php endif; ?>
                </nav>
            </div>
        </section>

        <?php if ($selectedTopic !== '' || $selectedAuthor !== ''): ?>
            <div class="sidebar-actions">
                <a class="btn btn-primary btn-sm" href="/" title="Показать все посты">Сбросить фильтры</a>
            </div>
        <?php endif; ?>
    </aside>
</div>
