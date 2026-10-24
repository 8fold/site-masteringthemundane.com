<?php

declare(strict_types=1);

namespace MTM\Marketing\UserInterface;

use MTM\PageMetadata;
use MTM\PageMetadataLoader;
use MTM\Shared\UserInterface\DocumentBase;

/**
 * Composes route metadata and compiled content into the shared document.
 */
final class PageRenderer
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
        return (string) DocumentBase::create(
            $route,
            $meta['titles'],
            $meta['description'],
        )->content(ContentFragment::forRoute($route));
    }

    private static function read(string $route, string $root): PageMetadata
    {
        $path = ContentFragment::directory($route, $root) . 'meta.json';
        return PageMetadataLoader::fromFile($path);
    }
}
