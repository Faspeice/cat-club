<?php
/** @var string $title */
/** @var string $description */
/** @var string $content */
/** @var array<string,mixed> $og */
/** @var array<string,mixed> $schema */
/** @var array<int,array{label:string,url:string}> $breadcrumbs */
/** @var array<string,mixed> $user */
/** @var string $layout */

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

$title = $title ?? 'КэтКлуб';
$description = $description ?? 'Клуб любителей кошек: питомцы, события, форум и лента активностей.';
$og = $og ?? [];
$schema = $schema ?? [];
$breadcrumbs = $breadcrumbs ?? [];
$user = $user ?? ['is_auth' => false];
$layout = $layout ?? 'default';

$ogTitle = (string)($og['title'] ?? $title);
$ogDesc = (string)($og['description'] ?? $description);
$ogType = (string)($og['type'] ?? 'website');
$ogUrl = (string)($og['url'] ?? absUrl($_SERVER['REQUEST_URI'] ?? '/'));
$ogImage = (string)($og['image'] ?? absUrl('/assets/img/placeholder-cat.svg'));
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= e($title) ?></title>
    <meta name="description" content="<?= e($description) ?>">

    <meta property="og:title" content="<?= e($ogTitle) ?>">
    <meta property="og:description" content="<?= e($ogDesc) ?>">
    <meta property="og:type" content="<?= e($ogType) ?>">
    <meta property="og:url" content="<?= e($ogUrl) ?>">
    <meta property="og:image" content="<?= e($ogImage) ?>">

    <link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="/assets/css/app.css">
    <script defer src="/assets/js/app.js"></script>

    <?php if (!empty($schema)): ?>
        <script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
    <?php endif; ?>
</head>
<body>
<a class="skip-link" href="#main">Перейти к содержимому</a>

<?php if ($layout !== 'auth'): ?>
    <?php require __DIR__ . '/partials/header.php'; ?>
<?php endif; ?>

<div class="container" style="<?= $layout === 'auth' ? 'max-width:none;margin:0;padding:0' : '' ?>">
    <?php if ($layout !== 'auth' && !empty($breadcrumbs)): ?>
        <?php require __DIR__ . '/partials/breadcrumbs.php'; ?>
    <?php endif; ?>

    <main id="main">
        <?= $content ?>
    </main>

    <?php if ($layout !== 'auth'): ?>
        <?php require __DIR__ . '/partials/footer.php'; ?>
    <?php endif; ?>
</div>
</body>
</html>

