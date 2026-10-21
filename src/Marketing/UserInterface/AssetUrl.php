<?php

declare(strict_types=1);

namespace MTM\Marketing\UserInterface;

use InvalidArgumentException;
use RuntimeException;

final class AssetUrl
{
    public static function versioned(string $path): string
    {
        if (preg_match('~\A/(?:[a-zA-Z0-9_-]+/)*[a-zA-Z0-9_.-]+\z~', $path) !== 1) {
            throw new InvalidArgumentException('Expected a local absolute asset path.');
        }
        $file = dirname(__DIR__, 3) . '/site-root/public' . $path;
        if (!is_file($file)) {
            throw new RuntimeException('Missing public asset: ' . $path);
        }
        $hash = hash_file('sha256', $file);
        if ($hash === false) {
            throw new RuntimeException('Could not hash public asset: ' . $path);
        }
        return $path . '?v=' . $hash;
    }
}
