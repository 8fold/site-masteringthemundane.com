<?php

declare(strict_types=1);

namespace MTM\Tests;

use Attribute;
use Error;
use MTM\PageMetadata;
use MTM\PageMetadataLoader;
use MTM\Shared\Attributes\DataObject;
use MTM\Shared\Attributes\Record;
use MTM\Shared\Attributes\ValueObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
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
            $metadata = PageMetadataLoader::fromFile($path);
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
            PageMetadataLoader::fromFile($path);
        } finally {
            unlink($path);
        }
    }

    public function testMetadataExposesOnlyImmutablePropertiesAndConstruction(): void
    {
        $class = new ReflectionClass(PageMetadata::class);
        self::assertTrue($class->isFinal());
        self::assertTrue($class->isReadOnly());
        self::assertSame(
            ['__construct'],
            array_map(
                static fn(ReflectionMethod $method): string => $method->getName(),
                $class->getMethods()
            )
        );
        foreach ($class->getProperties() as $property) {
            self::assertTrue($property->isPublic());
            self::assertTrue($property->isPromoted());
            self::assertTrue($property->isReadOnly());
            self::assertFalse($property->isStatic());
        }
        $metadata = new PageMetadata('Title', 'Description');
        parent::expectException(Error::class);
        $class->getProperty('title')->setValue($metadata, 'Changed');
    }

    public function testAllThreeMarkersAreInstantiableClassOnlyAttributes(): void
    {
        foreach ([DataObject::class, ValueObject::class, Record::class] as $marker) {
            $class = new ReflectionClass($marker);
            self::assertTrue($class->isFinal());
            self::assertSame([], $class->getProperties());
            self::assertSame([], $class->getMethods());
            $attributes = $class->getAttributes(Attribute::class);
            self::assertCount(1, $attributes);
            self::assertSame(Attribute::TARGET_CLASS, $attributes[0]->newInstance()->flags);
            self::assertInstanceOf($marker, new $marker());
        }
        $attributes = (new ReflectionClass(PageMetadata::class))->getAttributes();
        self::assertCount(1, $attributes);
        self::assertInstanceOf(DataObject::class, $attributes[0]->newInstance());
    }
}
