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
if (!function_exists('formatEventDate')) {
function formatEventDate(string $dateTime, string $format = 'd'): string
{
    if (empty($dateTime)) {
        return '';
    }

    $months = [
        '01' => 'янв', '02' => 'фев', '03' => 'мар', '04' => 'апр',
        '05' => 'май', '06' => 'июн', '07' => 'июл', '08' => 'авг',
        '09' => 'сен', '10' => 'окт', '11' => 'ноя', '12' => 'дек'
    ];

    $date = new DateTime($dateTime);

    if ($format === 'day') {
        return $date->format('d');
    }

    if ($format === 'month') {
        $monthNum = $date->format('m');
        return $months[$monthNum];
    }

    return '';
}
}

