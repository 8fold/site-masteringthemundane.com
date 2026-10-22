<?php

declare(strict_types=1);

namespace MTM\Marketing;

use JsonException;
use MTM\Marketing\UserInterface\ContentFragment;
use MTM\Marketing\UserInterface\PageMetadata;
use RuntimeException;
use stdClass;

final class StructuredData
{
    private const ORIGIN = 'https://mastering-the-mundane.com';
    private const PROVIDER_URL = 'https://itsjoshbruce.com';

    public static function compile(string $route, ?string $root = null): string
    {
        $meta = PageMetadata::forRoute($route, $root);
        $url = self::ORIGIN . $route;
        $site = self::ORIGIN . '/#website';
        $provider = self::PROVIDER_URL . '/#person';
        $page = [
            '@type' => 'WebPage',
            '@id' => $url . '#webpage',
            'url' => $url,
            'name' => $meta['titles'][0],
            'description' => $meta['description'],
            'isPartOf' => ['@id' => $site]
        ];
        $graph = [
            [
                '@type' => 'WebSite',
                '@id' => $site,
                'url' => self::ORIGIN . '/',
                'name' => $meta['titles'][count($meta['titles']) - 1],
                'publisher' => ['@id' => $provider]
            ],
            [
                '@type' => 'Person',
                '@id' => $provider,
                'name' => 'Joshua Bruce',
                'url' => self::PROVIDER_URL
            ]
        ];
        if ($route === '/coaching-and-mentoring/') {
            $page['mainEntity'] = ['@id' => $url . '#service'];
            $graph[] = [
                '@type' => 'Service',
                '@id' => $url . '#service',
                'url' => $url,
                'name' => $meta['titles'][0],
                'description' => $meta['description'],
                'provider' => ['@id' => $provider]
            ];
        }
        $graph[] = $page;
        return json_encode(
            ['@context' => 'https://schema.org', '@graph' => $graph],
            JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES |
                JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        ) . "\n";
    }

    public static function forRoute(string $route, ?string $root = null): string
    {
        $root ??= dirname(__DIR__, 2);
        $path = ContentFragment::directory($route, $root) . 'structured-data.json';
        if (!is_file($path)) {
            throw new RuntimeException($path . ': missing artifact; run composer compile-content.');
        }
        $json = file_get_contents($path);
        if ($json === false) {
            throw new RuntimeException($path . ': could not read artifact.');
        }
        try {
            $data = json_decode($json, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException(
                $path . ': invalid JSON; run composer compile-content.',
                0,
                $exception
            );
        }
        if (
            !$data instanceof stdClass ||
            ($data->{'@context'} ?? null) !== 'https://schema.org' ||
            !isset($data->{'@graph'}) ||
            !is_array($data->{'@graph'}) ||
            $data->{'@graph'} === [] ||
            str_contains($json, '<')
        ) {
            throw new RuntimeException(
                $path . ': invalid structured data artifact; run composer compile-content.'
            );
        }
        return $json;
    }
}
