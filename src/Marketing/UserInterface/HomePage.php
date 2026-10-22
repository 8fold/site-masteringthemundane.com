<?php

declare(strict_types=1);

namespace MTM\Marketing\UserInterface;

use JsonException;
use RuntimeException;
use stdClass;

final class HomePage
{
    /** @return array{heading: string, title: string, description: string} */
    public static function metadata(): array
    {
        $path = dirname(__DIR__, 3) . '/public_content/meta.json';
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
        foreach (['heading', 'title', 'description'] as $field) {
            if (!isset($meta->$field) || !is_string($meta->$field) || trim($meta->$field) === '') {
                throw new RuntimeException($path . ': ' . $field . ' must be a non-empty string.');
            }
        }
        return [
            'heading' => $meta->heading,
            'title' => $meta->title,
            'description' => $meta->description
        ];
    }

    public static function render(): string
    {
        $meta = self::metadata();
        return ReadingPage::render(
            '/',
            $meta['heading'],
            $meta['title'],
            $meta['description']
        );
    }
}
