<?php

declare(strict_types=1);

namespace MTM;

use JsonException;
use MTM\Shared\Attributes\DataObject;
use RuntimeException;
use stdClass;

#[DataObject]
final readonly class PageMetadata
{
    public function __construct(
        public string $title,
        public string $description
    ) {
    }

    public static function fromFile(string $path): self
    {
        if (!is_file($path)) {
            throw new RuntimeException($path . ': missing metadata.');
        }
        $json = file_get_contents($path);
        if ($json === false) {
            throw new RuntimeException($path . ': could not read metadata.');
        }
        try {
            $meta = json_decode($json, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException($path . ': ' . $exception->getMessage(), 0, $exception);
        }
        if (!$meta instanceof stdClass) {
            throw new RuntimeException($path . ': expected a metadata object.');
        }
        foreach (['title', 'description'] as $field) {
            if (!isset($meta->$field) || !is_string($meta->$field) || trim($meta->$field) === '') {
                throw new RuntimeException($path . ': ' . $field . ' must be a non-empty string.');
            }
        }
        if (isset($meta->heading)) {
            throw new RuntimeException(
                $path . ': heading is not supported; the h1 is the page title.'
            );
        }
        return new self($meta->title, $meta->description);
    }
}
