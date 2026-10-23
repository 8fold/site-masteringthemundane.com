<?php

declare(strict_types=1);

namespace MTM\Tests;

use DOMDocument;
use DOMXPath;
use MTM\Marketing\StructuredData;
use MTM\Marketing\UserInterface\ContentFragment;
use MTM\Marketing\UserInterface\PageMetadata;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class StructuredDataTest extends TestCase
{
    public function testEveryArtifactIsDeterministicApplicableAndEmbeddedExactly(): void
    {
        foreach (PageMetadata::ROUTES as $route) {
            $json = StructuredData::forRoute($route);
            self::assertSame($json, StructuredData::compile($route));
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            self::assertIsArray($data);
            self::assertSame('https://schema.org', $data['@context']);
            self::assertIsArray($data['@graph']);
            $types = array_column($data['@graph'], '@type');
            self::assertSame(
                $route === '/coaching-and-mentoring/'
                    ? ['WebSite', 'Person', 'Service', 'WebPage']
                    : ['WebSite', 'Person', 'WebPage'],
                $types
            );
            $page = $data['@graph'][count($data['@graph']) - 1];
            self::assertIsArray($page);
            $meta = PageMetadata::forRoute($route);
            self::assertSame($meta['titles'][0], $page['name']);
            self::assertSame($meta['description'], $page['description']);
            self::assertSame('https://mastering-the-mundane.com' . $route, $page['url']);
            self::assertStringNotContainsString('datePublished', $json);
            self::assertStringNotContainsString('dateModified', $json);
            self::assertStringNotContainsString('CoachingService', $json);
            $previous = libxml_use_internal_errors(true);
            try {
                $document = new DOMDocument();
                self::assertTrue($document->loadHTML(PageMetadata::render($route)));
                $xpath = new DOMXPath($document);
                self::assertSame(
                    1.0,
                    $xpath->evaluate('count(//script[@type="application/ld+json"])')
                );
                self::assertSame(
                    $json,
                    $xpath->evaluate('string(//script[@type="application/ld+json"])')
                );
            } finally {
                libxml_clear_errors();
                libxml_use_internal_errors($previous);
            }
        }
    }

    public function testUnsafeTextAndMissingMalformedArtifactsFailExplicitly(): void
    {
        $root = sys_get_temp_dir() . '/mtm-schema-' . bin2hex(random_bytes(8));
        $directory = $root . '/public_content/';
        mkdir($directory, 0700, true);
        $path = $directory . 'structured-data.json';
        $text = '</script><script>alert("A&B")</script>';
        try {
            file_put_contents(
                $directory . 'meta.json',
                json_encode(['title' => $text, 'description' => $text], JSON_THROW_ON_ERROR)
            );
            $json = StructuredData::compile('/', $root);
            self::assertStringNotContainsString('<', $json);
            self::assertStringNotContainsString('&', $json);
            self::assertStringContainsString('\u003C/script\u003E', $json);
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            self::assertIsArray($data);
            self::assertIsArray($data['@graph']);
            self::assertSame($text, $data['@graph'][2]['name']);
            foreach (
                [null, '{', '{}', '{"@context":"https://schema.org","@graph":[]}',
                    str_replace('\u003C', '<', $json)] as $contents
            ) {
                if ($contents !== null) {
                    file_put_contents($path, $contents);
                }
                try {
                    StructuredData::forRoute('/', $root);
                    self::fail('Expected artifact failure.');
                } catch (RuntimeException $exception) {
                    self::assertStringStartsWith($path . ': ', $exception->getMessage());
                    self::assertStringContainsString(
                        'run composer compile-content',
                        $exception->getMessage()
                    );
                }
            }
            file_put_contents($path, $json);
            file_put_contents($directory . 'meta.json', '{');
            self::assertSame($json, StructuredData::forRoute('/', $root));
            parent::expectException(RuntimeException::class);
            parent::expectExceptionMessage($directory . 'meta.json: Syntax error');
            StructuredData::compile('/', $root);
        } finally {
            unlink($path);
            unlink($directory . 'meta.json');
            rmdir($directory);
            rmdir($root);
        }
    }

    public function testArtifactsArePrivateSiblings(): void
    {
        foreach (PageMetadata::ROUTES as $route) {
            self::assertFileExists(
                ContentFragment::directory($route, dirname(__DIR__)) . 'structured-data.json'
            );
            self::assertFileDoesNotExist(
                dirname(__DIR__) . '/public' . $route . 'structured-data.json'
            );
        }
    }
}
