<?php

declare(strict_types=1);

namespace MTM\Tests;

use MTM\PageMetadata;
use MTM\Shared\Attributes\DataObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class PageMetadataTest extends TestCase
{
    public function testMetadataHydratesFromJsonAndIsMarkedAsADataObject(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'mtm-page-metadata-');
        self::assertIsString($path);
        try {
            self::assertNotFalse(file_put_contents(
                $path,
                '{"title":"Services","description":"Practical support."}'
            ));
            $metadata = PageMetadata::fromFile($path);
            self::assertSame('Services', $metadata->title);
            self::assertSame('Practical support.', $metadata->description);
            self::assertNotEmpty(
                (new \ReflectionClass(PageMetadata::class))->getAttributes(DataObject::class)
            );
        } finally {
            unlink($path);
        }
    }

    public function testInvalidMetadataNamesItsSourceFile(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'mtm-page-metadata-');
        self::assertIsString($path);
        try {
            self::assertNotFalse(file_put_contents($path, '{"title":"Services"}'));
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage($path . ': description must be a non-empty string.');
            PageMetadata::fromFile($path);
        } finally {
            unlink($path);
        }
    }
}
