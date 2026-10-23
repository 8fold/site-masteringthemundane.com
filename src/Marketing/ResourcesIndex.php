<?php

declare(strict_types=1);

namespace MTM\Marketing;

use DateTimeImmutable;
use JsonException;
use RuntimeException;
use stdClass;

final class ResourcesIndex
{
    public static function compile(string $root): string
    {
        $manifest = $root . '/public_content/resources/published.json';
        $routes = self::readJson($manifest);
        if (!is_array($routes) || !array_is_list($routes) || $routes === []) {
            throw new RuntimeException(
                $manifest . ': expected a non-empty list of published routes.'
            );
        }

        $items = [];
        $seen = [];
        foreach ($routes as $route) {
            if (
                !is_string($route) ||
                preg_match('~\A/(?:[a-z0-9]+(?:-[a-z0-9]+)*/)+\z~', $route) !== 1 ||
                in_array($route, $seen, true)
            ) {
                throw new RuntimeException($manifest . ': invalid or duplicate published route.');
            }
            $seen[] = $route;
            $directory = $root . '/public_content' . $route;
            $path = $directory . 'meta.json';
            if (
                !is_file($root . '/public' . $route . 'index.php') ||
                !is_file($directory . 'content.html')
            ) {
                throw new RuntimeException(
                    $path . ': missing published entry point or content.html.'
                );
            }
            $meta = self::readJson($path);
            if (!$meta instanceof stdClass) {
                throw new RuntimeException($path . ': expected a metadata object.');
            }
            if (
                !isset($meta->listingTitle) ||
                !is_string($meta->listingTitle) ||
                trim($meta->listingTitle) === ''
            ) {
                throw new RuntimeException($path . ': listingTitle must be a non-empty string.');
            }
            if (
                !isset($meta->type) ||
                !in_array($meta->type, ['model', 'exercise', 'guide', 'reference', 'essay'], true)
            ) {
                throw new RuntimeException(
                    $path . ': type must be model, exercise, guide, reference or essay.'
                );
            }
            if (
                !isset($meta->domain) ||
                !in_array($meta->domain, ['Time', 'People', 'Space', 'Money'], true)
            ) {
                throw new RuntimeException(
                    $path . ': domain must be Time, People, Space or Money.'
                );
            }
            if (!isset($meta->tags) || !is_array($meta->tags) || !array_is_list($meta->tags)) {
                throw new RuntimeException(
                    $path . ': tags must be a list of non-empty strings.'
                );
            }
            foreach ($meta->tags as $tag) {
                if (!is_string($tag) || trim($tag) === '') {
                    throw new RuntimeException(
                        $path . ': tags must be a list of non-empty strings.'
                    );
                }
            }
            if (
                !isset($meta->updated) ||
                !is_string($meta->updated) ||
                preg_match('/\A[0-9]{8}\z/', $meta->updated) !== 1
            ) {
                throw new RuntimeException($path . ': updated must be a valid YYYYMMDD date.');
            }
            $updated = DateTimeImmutable::createFromFormat('!Ymd', $meta->updated);
            if ($updated === false || $updated->format('Ymd') !== $meta->updated) {
                throw new RuntimeException($path . ': updated must be a valid YYYYMMDD date.');
            }
            $html = '<li><a href="' . self::escape($route) . '">'
                . self::escape($meta->listingTitle) . '</a> - '
                . ucfirst($meta->type) . ' / ' . $meta->domain
                . '<br>Last updated: <time datetime="' . $updated->format('Y-m-d') . '">'
                . $updated->format('F j, Y') . '</time></li>';
            $items[] = [
                'updated' => $updated->format('Ymd'),
                'title' => $meta->listingTitle,
                'route' => $route,
                'html' => $html
            ];
        }

        usort(
            $items,
            static fn(array $a, array $b): int =>
                strcmp($b['updated'], $a['updated'])
                ?: strcasecmp($a['title'], $b['title'])
                ?: strcmp($a['route'], $b['route'])
        );

        return "<ul>\n" . implode("\n", array_column($items, 'html')) . "\n</ul>\n";
    }

    private static function readJson(string $path): mixed
    {
        if (!is_file($path)) {
            throw new RuntimeException($path . ': missing JSON file.');
        }
        $json = file_get_contents($path);
        if ($json === false) {
            throw new RuntimeException($path . ': could not read JSON.');
        }
        try {
            return json_decode($json, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException($path . ': ' . $exception->getMessage(), 0, $exception);
        }
    }

    private static function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
