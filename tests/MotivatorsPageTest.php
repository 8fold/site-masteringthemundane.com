<?php

declare(strict_types=1);

namespace MTM\Tests;

use DOMDocument;
use DOMXPath;
use MTM\Marketing\UserInterface\ContentFragment;
use MTM\Marketing\UserInterface\MotivatorsPage;
use PHPUnit\Framework\TestCase;

final class MotivatorsPageTest extends TestCase
{
    public function testPhysicalRouteUsesCompiledContentWithoutRuntimeMarkdown(): void
    {
        $file = dirname(__DIR__) . '/site-root/public/resources/motivators/index.php';
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
        self::assertSame(MotivatorsPage::render(), $html);
        self::assertStringContainsString(
            ContentFragment::forRoute('/resources/motivators/'),
            MotivatorsPage::render()
        );
    }

    public function testReadingShellAndExerciseStructure(): void
    {
        $xpath = $this->page();
        foreach (
            [
            '//html[@lang="en-US"]',
            '//a[@href="#main"][@is="skip-link"]',
            '//header[@is="nav-header"]/h1[not(*)]',
            '//main[@id="main"][@tabindex="-1"]',
            '//footer',
            '//nav[@aria-label="Menu"]'
            ] as $query
        ) {
            self::assertSame(1.0, $xpath->evaluate('count(' . $query . ')'));
        }
        self::assertSame(1.0, $xpath->evaluate('count(//h1)'));
        self::assertSame(
            'The Motivators exercise by Josh Bruce',
            $xpath->evaluate('string(//header/h1)')
        );
        self::assertSame(0.0, $xpath->evaluate('count(//main//article|//main//h1)'));
        self::assertSame(2.0, $xpath->evaluate('count(//main/dl)'));
        self::assertSame(8.0, $xpath->evaluate('count(//main/dl[1]/dt)'));
        self::assertSame(5.0, $xpath->evaluate('count(//main/dl[2]/dt)'));
        self::assertSame(13.0, $xpath->evaluate('count(//main/dl/dd)'));
        self::assertSame(
            0.0,
            $xpath->evaluate('count(//main/dl/dt[not(following-sibling::*[1][self::dd])])')
        );
        self::assertGreaterThan(0.0, $xpath->evaluate('count(//main/h2)'));
        $group = '//main/section[@is="group-application"]';
        self::assertSame(1.0, $xpath->evaluate('count(' . $group . ')'));
        self::assertSame(
            'for-the-group',
            $xpath->evaluate('string(' . $group . '/@aria-labelledby)')
        );
        self::assertSame(
            1.0,
            $xpath->evaluate('count(' . $group . '/h2[@id="for-the-group"])')
        );
        self::assertSame(
            'For groups',
            $xpath->evaluate('string(' . $group . '/h2)')
        );
        self::assertGreaterThan(0.0, $xpath->evaluate('count(' . $group . '/p)'));
        self::assertSame(0.0, $xpath->evaluate('count(' . $group . '//dl)'));
        self::assertSame(
            1.0,
            $xpath->evaluate('count(' . $group . '/following-sibling::h2)')
        );
        self::assertGreaterThan(0.0, $xpath->evaluate('count(//main/ol/li)'));
        self::assertSame(
            'Chief Executive Officer',
            $xpath->evaluate('string(//main//abbr/@title)')
        );
        self::assertSame(0.0, $xpath->evaluate('count(//nav[@aria-label="Read"])'));
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
        // A detail page is not the Resources index; do not mark that link as the current page.
        self::assertSame(0.0, $xpath->evaluate('count(//nav//a[@aria-current])'));
        self::assertSame(
            1.0,
            $xpath->evaluate('count(//nav[@aria-label="Menu"]//a[@href="/resources/"])')
        );
        self::assertSame(
            '© 2023–' . date('Y') . ' Mastering the Mundane',
            $xpath->evaluate('string(//footer)')
        );
        self::assertSame(0.0, $xpath->evaluate('count(//form|//iframe|//script[@src])'));
        RenderedAnchorAssertions::assertHttpHostPolicy(
            $xpath,
            'https://mastering-the-mundane.com/resources/motivators/'
        );
    }

    public function testExistingPageMetadataAndSharedStylesheet(): void
    {
        $xpath = $this->page();
        $title = 'The Motivators exercise by Josh Bruce | Resources'
            . ' | Mastering the Mundane';
        $description = 'Discover what truly motivates you. The Motivators exercise clarifies '
            . 'your core drivers across 8 dimensions—for self-discovery, team building, '
            . 'and intentional life design.';
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

    public function testPublicationDatesKeepTheirMeaningAndReadingOrder(): void
    {
        $xpath = $this->page();
        $dates = '//main/div[@is="publication-dates"]';
        self::assertSame(1.0, $xpath->evaluate('count(' . $dates . ')'));
        self::assertSame(2.0, $xpath->evaluate('count(' . $dates . '/p/time)'));
        self::assertSame(
            '2026-05-11',
            $xpath->evaluate('string(' . $dates . '/p[1]/time/@datetime)')
        );
        self::assertSame(
            '2018-09-16',
            $xpath->evaluate('string(' . $dates . '/p[2]/time/@datetime)')
        );
        self::assertSame('Updated:', $xpath->evaluate('string(' . $dates . '/p[1]/b)'));
        self::assertSame('Original:', $xpath->evaluate('string(' . $dates . '/p[2]/b)'));
    }

    private function page(): DOMXPath
    {
        $previous = libxml_use_internal_errors(true);
        try {
            $document = new DOMDocument();
            self::assertTrue($document->loadHTML(MotivatorsPage::render()));
            return new DOMXPath($document);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }
}
