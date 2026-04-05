<?php

declare(strict_types=1);

if (!function_exists('e')) {
    function e(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('absUrl')) {
    function absUrl(string $path): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $path = '/' . ltrim($path, '/');
        return $scheme . '://' . $host . $path;
    }
}
if (!function_exists('feedSidebarUrl')) {
    function feedSidebarUrl(string $topic, string $author, int $topicPage, int $memberPage): string
    {
        $q = [];
        if ($topic !== '') {
            $q['topic'] = $topic;
        }
        if ($author !== '') {
            $q['author'] = $author;
        }
        if ($topicPage > 1) {
            $q['tp'] = (string)$topicPage;
        }
        if ($memberPage > 1) {
            $q['mp'] = (string)$memberPage;
        }
        $s = http_build_query($q);

        return $s === '' ? '/' : ('/?' . $s);
    }
}

if (!function_exists('currentFeedRedirect')) {
    function currentFeedRedirect(): string
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            $path = '/';
        }
        $qs = $_SERVER['QUERY_STRING'] ?? '';
        if (!is_string($qs)) {
            $qs = '';
        }
        $out = $path === '' ? '/' : $path;
        if ($qs !== '') {
            $out .= '?' . $qs;
        }
        if (mb_strlen($out) > 500) {
            return '/';
        }

        return $out;
    }
}

if (!function_exists('formatEventDate')) {
function formatEventDate(string $dateTime, string $format = 'd'): string
{
    if (empty($dateTime)) {
        return '';
    }

    /* Именительный падеж — как на макете карточки события (число отдельно, месяц под ним) */
    $months = [
        '01' => 'январь', '02' => 'февраль', '03' => 'март', '04' => 'апрель',
        '05' => 'май', '06' => 'июнь', '07' => 'июль', '08' => 'август',
        '09' => 'сентябрь', '10' => 'октябрь', '11' => 'ноябрь', '12' => 'декабрь'
    ];

    try {
        $date = new DateTime($dateTime);
    } catch (\Throwable) {
        return '';
    }

    if ($format === 'day') {
        return $date->format('d');
    }

    if ($format === 'month') {
        $monthNum = $date->format('m');
        return $months[$monthNum] ?? '';
    }

    return '';
}
}

