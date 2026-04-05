<?php
/** @var array<int,array<string,mixed>> $events */
declare(strict_types=1);
?>
<header class="page-header">
    <h1 class="page-title">События клуба</h1>
    <?php if (!empty($user['is_auth'])): ?>
        <div class="page-header-actions">
            <button type="button" class="btn btn-primary btn-sm" data-open-panel="add-event" aria-expanded="false" aria-controls="add-event" title="Открыть форму нового события">Добавить событие</button>
        </div>
    <?php endif; ?>
</header>

<?php if (!empty($user['is_auth'])): ?>
    <section class="card feed-add-post" id="add-event" hidden aria-label="Добавить событие">
        <h2 class="sidebar-title" style="margin-bottom:10px">Новое событие</h2>
        <form action="/api/events/create" method="post">
            <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
            <div class="filter-row">
                <div class="field" style="flex:1;min-width:240px">
                    <label class="label" for="ev-title">Название</label>
                    <input class="input" id="ev-title" name="title" type="text" required>
                </div>
                <div class="field" style="flex:1;min-width:240px">
                    <label class="label" for="ev-place">Место</label>
                    <input class="input" id="ev-place" name="place" type="text">
                </div>
            </div>
            <div class="filter-row">
                <div class="field">
                    <label class="label" for="ev-start">Начало (ISO)</label>
                    <input class="input" id="ev-start" name="starts_at" type="text" placeholder="2026-03-17 19:00:00+03" required>
                </div>
                <div class="field">
                    <label class="label" for="ev-end">Окончание (ISO)</label>
                    <input class="input" id="ev-end" name="ends_at" type="text" placeholder="2026-03-17 21:00:00+03">
                </div>
            </div>
            <div class="field" style="margin-bottom:12px">
                <label class="label" for="ev-body">Описание</label>
                <textarea class="textarea" id="ev-body" name="body"></textarea>
            </div>
            <button class="btn btn-primary" type="submit" title="Создать событие">Создать</button>
        </form>
    </section>
<?php endif; ?>

<?php if (empty($events)): ?>
    <div class="card">
        <p>Событий пока нет.</p>
    </div>
<?php else: ?>
    <div class="events-grid">
        <?php foreach ($events as $ev): ?>
            <article class="event-card">
                <div class="event-content">
                    <h2 class="event-title"><?= e((string)($ev['title'] ?? '')) ?></h2>
                    <p class="event-description"><?= e((string)($ev['body'] ?? '')) ?></p>
                    <div class="event-details">
                        <div><strong>Место:</strong> <?= e((string)($ev['place'] ?? '')) ?></div>
                        <div><strong>Начало:</strong> <?= e((string)($ev['starts_at'] ?? '')) ?></div>
                    </div>
                </div>
                <div class="event-date-badge" aria-label="Дата события">
                    <span class="event-date-day"><?= formatEventDate($ev['starts_at'] ?? '', 'day') ?></span>
                    <span class="event-date-month"><?= formatEventDate($ev['starts_at'] ?? '', 'month') ?></span>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
