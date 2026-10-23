<?php

declare(strict_types=1);

namespace MTM\Tests;

use InvalidArgumentException;
use MTM\Marketing\UserInterface\AssetUrl;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class AssetUrlTest extends TestCase
{
    public function testVersionIsTheContentHashAndChangesWhenTheFileChanges(): void
    {
        $path = '/assets/css/.asset-test-' . bin2hex(random_bytes(8)) . '.css';
        $file = dirname(__DIR__) . '/public' . $path;
        try {
            self::assertSame(3, file_put_contents($file, 'one'));
            $first = AssetUrl::versioned($path);
            self::assertSame($path . '?v=' . hash('xxh3', 'one'), $first);
            self::assertSame($first, AssetUrl::versioned($path));
            self::assertSame(3, file_put_contents($file, 'two'));
            self::assertNotSame($first, AssetUrl::versioned($path));
            self::assertSame($path . '?v=' . hash('xxh3', 'two'), AssetUrl::versioned($path));
        } finally {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    public function testMissingAssetFailsInsteadOfSilentlyShippingAnUnversionedUrl(): void
    {
        parent::expectException(RuntimeException::class);
        AssetUrl::versioned('/assets/css/does-not-exist.css');
    }

    public function testExternalAssetsAreNotAccepted(): void
    {
        parent::expectException(InvalidArgumentException::class);
        AssetUrl::versioned('https://example.com/tracker.css');
    }

    public function testAssetPathCannotTraverseOutsideWebroot(): void
    {
        parent::expectException(InvalidArgumentException::class);
        AssetUrl::versioned('/../../composer.json');
    }
}
