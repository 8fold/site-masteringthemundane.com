<?php

declare(strict_types=1);

namespace MTM\Marketing\UserInterface;

use InvalidArgumentException;
use RuntimeException;

final class ContentFragment
{
    public static function forRoute(string $route): string
    {
        if (preg_match('~\A/(?:[a-z0-9-]+/)+\z~', $route) !== 1) {
            throw new InvalidArgumentException(
                'Expected a physical content route with a trailing slash.'
            );
        }
        $path = dirname(__DIR__, 3) . '/public_content' . $route . 'content.html';
        if (
            in_array($route, [
                '/articles/the-action-loop/',
                '/tools-and-resources/motivators/',
                '/tools-and-resources/the-5-ps/'
            ], true)
        ) {
            $path = dirname(__DIR__, 3) . '/site-root/public' . $route . 'content.html';
        }
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
}
