<?php
/**
 * @var int $page
 * @var int $pages
 * @var string $basePath
 * @var array<string,string> $query
 */
declare(strict_types=1);

if ($pages <= 1) {
    return;
}

function pageUrl(string $basePath, array $query, int $p): string
{
    $q = $query;
    $q['page'] = (string)$p;
    $qs = http_build_query($q);
    return $basePath . ($qs ? ('?' . $qs) : '');
}
?>
<nav class="pagination" aria-label="Пагинация">
    <?php for ($p = 1; $p <= $pages; $p++): ?>
        <a class="page-link" href="<?= htmlspecialchars(pageUrl($basePath, $query, $p), ENT_QUOTES) ?>"
           title="Страница <?= $p ?>"
            <?= $p === $page ? 'aria-current="page"' : '' ?>
        ><?= $p ?></a>
    <?php endfor; ?>
</nav>

