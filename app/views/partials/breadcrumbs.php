<?php
/** @var array<int,array{label:string,url:string}> $breadcrumbs */
declare(strict_types=1);
?>
<nav class="breadcrumbs" aria-label="Хлебные крошки">
    <?php foreach ($breadcrumbs as $i => $bc): ?>
        <?php if ($i > 0): ?><span class="breadcrumb-sep">▶</span><?php endif; ?>
        <?php if ($i < count($breadcrumbs) - 1): ?>
            <a href="<?= htmlspecialchars($bc['url'], ENT_QUOTES) ?>" title="<?= htmlspecialchars($bc['label'], ENT_QUOTES) ?>"><?= htmlspecialchars($bc['label'], ENT_QUOTES) ?></a>
        <?php else: ?>
            <span class="breadcrumb-current"><?= htmlspecialchars($bc['label'], ENT_QUOTES) ?></span>
        <?php endif; ?>
    <?php endforeach; ?>
</nav>

