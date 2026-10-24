<?php

declare(strict_types=1);

namespace MTM\Marketing\UserInterface;

use JsonException;
use MTM\Shared\UserInterface\DocumentBase;
use RuntimeException;
use stdClass;

/**
 * Reads a reading page's title and description from the `meta.json` beside
 * its compiled content fragment. As in the apps site, the browser title is
 * the page's own title followed by each ancestor page's title.
 */
final class PageMetadata
{
    /** Routes whose page metadata is authored in `meta.json`. */
    public const ROUTES = [
        '/',
        '/coaching-and-mentoring/',
        '/services/',
        '/resources/',
        '/resources/the-action-loop/',
        '/resources/motivators/',
        '/resources/the-5-ps/',
        '/books/',
        '/books/time-mastering-the-mundane/'
    ];

    /** @return array{titles: non-empty-list<string>, description: string} */
    public static function forRoute(string $route, ?string $root = null): array
    {
        $root ??= dirname(__DIR__, 3);
        $page = self::read($route, $root);
        $titles = [$page->title];
        $ancestor = $route;
        while ($ancestor !== '/') {
            $ancestor = rtrim(dirname($ancestor), '/') . '/';
            $titles[] = self::read($ancestor, $root)->title;
        }
        return ['titles' => $titles, 'description' => $page->description];
    }

    public static function render(string $route): string
    {
        $meta = self::forRoute($route);
        return DocumentBase::render(
            $route,
            $meta['titles'],
            $meta['description'],
            ContentFragment::forRoute($route)
        );
    }

    /** @return object{title: string, description: string} */
    private static function read(string $route, string $root): object
    {
        $path = ContentFragment::directory($route, $root) . 'meta.json';
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
        return (object) ['title' => $meta->title, 'description' => $meta->description];
    }
}
