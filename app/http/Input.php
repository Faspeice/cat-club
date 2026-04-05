<?php

declare(strict_types=1);

final class Input
{
    public static function str(array $source, string $key, int $maxLen = 5000): string
    {
        $val = $source[$key] ?? '';
        if (!is_string($val)) {
            return '';
        }
        $val = trim($val);
        if ($maxLen > 0 && mb_strlen($val) > $maxLen) {
            $val = mb_substr($val, 0, $maxLen);
        }
        return $val;
    }

    public static function int(array $source, string $key, int $default = 0): int
    {
        $val = $source[$key] ?? null;
        if (is_int($val)) {
            return $val;
        }
        if (is_string($val) && preg_match('/^-?\d+$/', $val)) {
            return (int)$val;
        }
        return $default;
    }

    public static function url(array $source, string $key, int $maxLen = 2000): string
    {
        $val = self::str($source, $key, $maxLen);
        if ($val === '') {
            return '';
        }
        if (!filter_var($val, FILTER_VALIDATE_URL)) {
            return '';
        }
        return $val;
    }
}

