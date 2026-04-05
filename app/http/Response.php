<?php

declare(strict_types=1);

final class Response
{
    public static function status(int $code): void
    {
        http_response_code($code);
    }

    public static function header(string $name, string $value): void
    {
        header($name . ': ' . $value);
    }

    public static function contentType(string $type): void
    {
        self::header('Content-Type', $type);
    }

    public static function lastModified(int $unixTs): bool
    {
        if ($unixTs <= 0) {
            return true;
        }

        $gmt = gmdate('D, d M Y H:i:s', $unixTs) . ' GMT';
        self::header('Last-Modified', $gmt);

        $ims = $_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? '';
        if (is_string($ims) && $ims !== '') {
            $imsTs = strtotime($ims);
            if ($imsTs !== false && $imsTs >= $unixTs) {
                self::status(304);
                return false;
            }
        }
        return true;
    }

    public static function redirect(string $to, int $code = 302): never
    {
        self::status($code);
        header('Location: ' . $to);
        exit;
    }

    public static function json(array $data, int $code = 200): never
    {
        self::status($code);
        self::contentType('application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

