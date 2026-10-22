<?php

declare(strict_types=1);

namespace MTM\Marketing\UserInterface;

use InvalidArgumentException;
use RuntimeException;

final class ContentFragment
{
    public static function forRoute(string $route): string
    {
        $path = self::directory($route, dirname(__DIR__, 3)) . 'content.html';
        if (!is_file($path)) {
            throw new RuntimeException(
                'Missing fragment for ' . $route . '; run composer compile-content.'
            );
        }
        $html = file_get_contents($path);
        if ($html === false) {
            throw new RuntimeException('Could not read fragment for ' . $route);
        }
        return $html;
    }

    /** Directory holding a route's compiled fragment and its sibling metadata. */
    public static function directory(string $route, string $root): string
    {
        if (preg_match('~\A/(?:[a-z0-9-]+/)*\z~', $route) !== 1) {
            throw new InvalidArgumentException(
                'Expected a physical content route with a trailing slash.'
            );
        }
        return $root . '/public_content' . $route;
    }
}
