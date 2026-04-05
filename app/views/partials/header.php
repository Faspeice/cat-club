<?php
/** @var array<string,mixed> $user */
declare(strict_types=1);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if (!is_string($path) || $path === '') {
    $path = '/';
}
?>
<header class="site-header" role="banner">
    <div class="logo"><a href="/" title="На главную">КэтКлуб</a></div>
    <nav class="site-nav" aria-label="Основная навигация">
        <a href="/" title="Лента активностей" <?= $path === '/' ? 'aria-current="page"' : '' ?>>Лента</a>
        <a href="/pets" title="Галерея питомцев" <?= str_starts_with($path, '/pets') ? 'aria-current="page"' : '' ?>>Питомцы</a>
        <a href="/events" title="События клуба" <?= str_starts_with($path, '/events') ? 'aria-current="page"' : '' ?>>События</a>
    </nav>
    <div class="header-actions">
        <?php if (!empty($user['is_auth'])): ?>
            <span class="btn btn-sm" title="Вы вошли как <?= htmlspecialchars((string)($user['nick'] ?? ''), ENT_QUOTES) ?>"><?= htmlspecialchars((string)($user['nick'] ?? 'Пользователь'), ENT_QUOTES) ?></span>
            <form action="/logout" method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES) ?>">
                <button class="btn btn-sm" type="submit" title="Выйти из аккаунта">Выход</button>
            </form>
        <?php else: ?>
            <a class="btn btn-sm" href="/login" title="Вход">Вход</a>
            <a class="btn btn-sm" href="/register" title="Регистрация">Регистрация</a>
        <?php endif; ?>
        <noscript>
            <div style="font-size:12px;opacity:.9">JavaScript отключён — сайт остаётся доступным.</div>
        </noscript>
    </div>
</header>

