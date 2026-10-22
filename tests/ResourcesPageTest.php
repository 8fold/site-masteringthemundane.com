<?php

declare(strict_types=1);

namespace MTM\Tests;

use DOMDocument;
use DOMXPath;
use MTM\Marketing\UserInterface\ResourcesPage;
use MTM\Marketing\UserInterface\ContentFragment;
use PHPUnit\Framework\TestCase;

final class ResourcesPageTest extends TestCase
{
    public function testPhysicalRouteRendersWithoutLoadingTheMarkdownConverter(): void
    {
        $file = dirname(__DIR__) . '/public/resources/index.php';
        $process = proc_open(
            [
                PHP_BINARY,
                '-r',
                'require $argv[1];'
                    . ' if (class_exists("Eightfold\\\\Markdown\\\\Markdown", false)) {'
                    . ' throw new RuntimeException("Runtime loaded the Markdown converter."); }',
                $file
            ],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            dirname($file)
        );
        self::assertIsResource($process);
        $html = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        self::assertSame(0, proc_close($process));
        self::assertSame('', $errors);
        self::assertSame(ResourcesPage::render(), $html);
    }

    public function testReadingLandmarksNavigationAndPublishedDestination(): void
    {
        $xpath = $this->page();
        foreach (
            [
            '//html[@lang="en-US"]',
            '//body/header/h1[not(*)]',
            '//a[@href="#main"][@is="skip-link"]',
            '//main[@id="main"][@tabindex="-1"]',
            '//footer',
            '//nav[@aria-label="Menu"]'
            ] as $query
        ) {
            self::assertSame(1.0, $xpath->evaluate('count(' . $query . ')'));
        }
        self::assertSame(1.0, $xpath->evaluate('count(//h1)'));
        self::assertSame(
            '/resources/the-5-ps/',
            $xpath->evaluate('string(//main//a/@href)')
        );
        self::assertSame(3.0, $xpath->evaluate('count(//main//a)'));
        foreach (
            [
            '/resources/the-action-loop/' => ['2024-02-02', 'February 2, 2024'],
            '/resources/motivators/' => ['2026-05-11', 'May 11, 2026'],
            '/resources/the-5-ps/' => ['2026-05-11', 'May 11, 2026']
            ] as $href => [$date, $label]
        ) {
            $item = '//main//li[a[@href="' . $href . '"]]';
            self::assertSame(1.0, $xpath->evaluate('count(' . $item . '/br)'));
            self::assertSame($date, $xpath->evaluate('string(' . $item . '/time/@datetime)'));
            self::assertSame($label, $xpath->evaluate('string(' . $item . '/time)'));
            $text = $xpath->evaluate('string(' . $item . ')');
            self::assertIsString($text);
            self::assertStringContainsString('Last updated: ' . $label, $text);
        }
        foreach (
            [
            '/resources/the-action-loop/' => 'Model / Time',
            '/resources/motivators/' => 'Exercise / People',
            '/resources/the-5-ps/' => 'Exercise / People'
            ] as $href => $taxonomy
        ) {
            self::assertSame(
                1.0,
                $xpath->evaluate('count(//main//li/a[@href="' . $href . '"])')
            );
            $item = $xpath->evaluate('string(//main//li[a[@href="' . $href . '"]])');
            self::assertIsString($item);
            self::assertStringContainsString($taxonomy, $item);
        }
        $linkText = $xpath->evaluate('string(//main//a)');
        self::assertIsString($linkText);
        self::assertNotSame('', trim($linkText));
        self::assertStringContainsString(
            ContentFragment::forRoute('/resources/'),
            ResourcesPage::render()
        );
        self::assertSame(1.0, $xpath->evaluate('count(//nav//a[@aria-current])'));
        self::assertSame(
            '/resources/',
            $xpath->evaluate('string(//nav//a[@aria-current="page"]/@href)')
        );
        self::assertSame(0.0, $xpath->evaluate('count(//nav[@aria-label="Read"])'));
        self::assertSame(
            1.0,
            $xpath->evaluate('count(//nav[@aria-label="Menu"]//a[@href="/books/"])')
        );
        self::assertSame(
            1.0,
            $xpath->evaluate('count(//nav[@aria-label="Menu"]//a[@href="/resources/"])')
        );
        self::assertSame(
            1.0,
            $xpath->evaluate(
                'count(//header/a[@href="https://apps.mastering-the-mundane.com"]'
                    . '[@target="_blank"][@rel="noopener noreferrer"])'
            )
        );
        self::assertSame(
            1.0,
            $xpath->evaluate(
                'count(//button[@type="button"][@popovertarget="primary-navigation"])'
            )
        );
        self::assertSame(
            1.0,
            $xpath->evaluate('count(//div[@id="primary-navigation"][@popover="auto"])')
        );
        self::assertSame(
            '© 2023–' . date('Y') . ' Mastering the Mundane',
            $xpath->evaluate('string(//footer)')
        );
        self::assertSame(0.0, $xpath->evaluate('count(//form|//iframe|//script[@src])'));
        RenderedAnchorAssertions::assertHttpHostPolicy(
            $xpath,
            'https://mastering-the-mundane.com/resources/'
        );
    }

    public function testArticlesLabelsAndMetadataAreConsistentWithoutAnSeoPolicyChange(): void
    {
        $xpath = $this->page();
        self::assertSame('Resources', $xpath->evaluate('string(//header/h1)'));
        $title = $xpath->evaluate('string(//title)');
        self::assertIsString($title);
        self::assertStringStartsWith('Resources | ', $title);
        $description = $xpath->evaluate('string(//meta[@name="description"]/@content)');
        self::assertIsString($description);
        self::assertNotSame('', trim($description));
        self::assertLessThanOrEqual(160, strlen($description));
        foreach (
            [
            'property="og:title"' => $title,
            'property="og:description"' => $description,
            'name="twitter:title"' => $title,
            'name="twitter:description"' => $description,
            'property="og:url"' => 'mastering-the-mundane.com',
            'property="twitter:domain"' => 'mastering-the-mundane.com',
            'property="twitter:url"' => 'https://mastering-the-mundane.com',
            'property="og:image"' => 'https://mastering-the-mundane.com/assets/media/poster.png',
            'name="twitter:image"' => 'https://mastering-the-mundane.com/assets/media/poster.png'
            ] as $selector => $value
        ) {
            self::assertSame(
                $value,
                $xpath->evaluate('string(//meta[@' . $selector . ']/@content)')
            );
        }
        self::assertSame(0.0, $xpath->evaluate('count(//link[@rel="canonical"])'));
        self::assertSame(1.0, $xpath->evaluate('count(//link[@rel="stylesheet"])'));
        $stylesheet = $xpath->evaluate('string(//link[@rel="stylesheet"]/@href)');
        self::assertIsString($stylesheet);
        self::assertMatchesRegularExpression(
            '~\A/assets/css/reading\.min\.css\?v=[a-f0-9]{16}\z~',
            $stylesheet
        );
    }

    private function page(): DOMXPath
    {
        $previous = libxml_use_internal_errors(true);
        try {
            $document = new DOMDocument();
            self::assertTrue($document->loadHTML(ResourcesPage::render()));
            return new DOMXPath($document);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }
}
