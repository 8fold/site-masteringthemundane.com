<?php

declare(strict_types=1);

namespace MTM\Tests;

use DOMDocument;
use DOMXPath;
use MTM\Marketing\UserInterface\ActionLoopPage;
use PHPUnit\Framework\TestCase;

final class ActionLoopPageTest extends TestCase
{
    public function testPhysicalRouteUsesCompiledContentWithoutRuntimeMarkdown(): void
    {
        $file = dirname(__DIR__) . '/site-root/public/resources/the-action-loop/index.php';
        $process = proc_open(
            [
                PHP_BINARY, '-r',
                'require $argv[1];'
                    . ' if (class_exists("Eightfold\\\\Markdown\\\\Markdown", false)) {'
                    . ' throw new RuntimeException("Runtime loaded Markdown."); }',
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
        self::assertSame(ActionLoopPage::render(), $html);
    }

    public function testReadingStructureNavigationAndDateDiscrepancyArePreserved(): void
    {
        $xpath = $this->page();
        foreach (
            [
            '//html[@lang="en-US"]',
            '//a[@href="#main"][@is="skip-link"]',
            '//header[@is="marketing-header"]/h1[not(*)]',
            '//main[@id="main"][@tabindex="-1"]',
            '//footer',
            '//nav[@aria-label="Menu"]',
            '//nav[@aria-label="Read"]'
            ] as $query
        ) {
            self::assertSame(1.0, $xpath->evaluate('count(' . $query . ')'));
        }
        self::assertSame(1.0, $xpath->evaluate('count(//h1)'));
        self::assertSame('Action loop', $xpath->evaluate('string(//header/h1)'));
        self::assertSame(0.0, $xpath->evaluate('count(//main//h1|//main//article)'));
        self::assertSame(5.0, $xpath->evaluate('count(//main/h2)'));
        self::assertGreaterThan(0.0, $xpath->evaluate('count(//main/ol/li)'));
        self::assertSame(0.0, $xpath->evaluate('count(//nav//a[@aria-current])'));
        self::assertSame(
            1.0,
            $xpath->evaluate('count(//nav[@aria-label="Read"]//a[@href="/resources/"])')
        );
        $dates = '//main/div[@is="publication-dates"]';
        self::assertSame(2.0, $xpath->evaluate('count(' . $dates . '/p/time)'));
        self::assertSame(
            '2024-02-02',
            $xpath->evaluate('string(' . $dates . '/p[1]/time/@datetime)')
        );
        self::assertSame(
            '2024-06-23',
            $xpath->evaluate('string(' . $dates . '/p[2]/time/@datetime)')
        );
        self::assertSame('Updated:', $xpath->evaluate('string(' . $dates . '/p[1]/b)'));
        self::assertSame('Original:', $xpath->evaluate('string(' . $dates . '/p[2]/b)'));
        self::assertSame(0.0, $xpath->evaluate('count(//form|//iframe|//script[@src])'));
        RenderedAnchorAssertions::assertHttpHostPolicy(
            $xpath,
            'https://mastering-the-mundane.com/resources/the-action-loop/'
        );
        self::assertSame(
            1.0,
            $xpath->evaluate('count(//main//a[@href="/books/"][not(@target)])')
        );
        self::assertSame(
            1.0,
            $xpath->evaluate(
                'count(//main//a[@target="_blank"][@rel="noopener noreferrer"])'
            )
        );
    }

    public function testExistingMetadataAndSharedStylesheet(): void
    {
        $xpath = $this->page();
        $title = 'The Action Loop: Work Sustainably and Get Things Done | Resources'
            . ' | Mastering the Mundane';
        $description = 'Master the 5-phase action loop to sustain energy, prevent burnout, '
            . 'and maintain peak productivity. Learn the framework for intentional, '
            . 'balanced progress.';
        self::assertSame($title, $xpath->evaluate('string(//title)'));
        foreach (
            [
            'name="description"' => $description,
            'property="og:title"' => $title,
            'property="og:description"' => $description,
            'name="twitter:title"' => $title,
            'name="twitter:description"' => $description,
            'property="og:url"' => 'mastering-the-mundane.com',
            'property="twitter:domain"' => 'mastering-the-mundane.com',
            'property="twitter:url"' => 'https://mastering-the-mundane.com',
            'property="og:image"' => 'https://mastering-the-mundane.com/media/poster.png',
            'name="twitter:image"' => 'https://mastering-the-mundane.com/media/poster.png'
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
            '~\A/css/reading\.css\?v=[a-f0-9]{64}\z~',
            $stylesheet
        );
    }

    private function page(): DOMXPath
    {
        $previous = libxml_use_internal_errors(true);
        try {
            $document = new DOMDocument();
            self::assertTrue($document->loadHTML(ActionLoopPage::render()));
            return new DOMXPath($document);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }
}
