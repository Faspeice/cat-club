<?php

declare(strict_types=1);

final class Router
{
    /**
     * @return array{path:string, segments:list<string>}
     */
    public static function parsePath(): array
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        if (!is_string($uri) || $uri === '') {
            $uri = '/';
        }
        $path = parse_url($uri, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            $path = '/';
        }
        $path = '/' . ltrim($path, '/');
        $path = rtrim($path, '/');
        if ($path === '') {
            $path = '/';
        }

        $segments = $path === '/' ? [] : explode('/', ltrim($path, '/'));
        $segments = array_values(array_filter($segments, static fn($s) => $s !== ''));

        return ['path' => $path, 'segments' => $segments];
    }
}

