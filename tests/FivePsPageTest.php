<?php

declare(strict_types=1);

namespace MTM\Tests;

use DOMDocument;
use DOMXPath;
use MTM\Marketing\UserInterface\FivePsPage;
use PHPUnit\Framework\TestCase;

final class FivePsPageTest extends TestCase
{
    public function testPhysicalRouteRendersWithoutRuntimeMarkdown(): void
    {
        $file = dirname(__DIR__) . '/public/resources/the-5-ps/index.php';
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
        self::assertSame(FivePsPage::render(), $html);
    }

    public function testReadingShellPreservesIndividualAndGroupExerciseStructure(): void
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
            'The 5 Ps exercise by Josh Bruce',
            $xpath->evaluate('string(//header/h1)')
        );
        self::assertSame(0.0, $xpath->evaluate('count(//main//h1|//main//article)'));
        self::assertSame(1.0, $xpath->evaluate('count(//main/dl)'));
        self::assertSame(5.0, $xpath->evaluate('count(//main/dl/dt)'));
        self::assertSame(5.0, $xpath->evaluate('count(//main/dl/dd)'));
        self::assertSame(
            0.0,
            $xpath->evaluate('count(//main/dl/dt[not(following-sibling::*[1][self::dd])])')
        );
        self::assertGreaterThan(0.0, $xpath->evaluate('count(//main/ul/li)'));
        $group = '//main/section[@is="group-application"][@aria-labelledby="for-groups"]';
        self::assertSame(1.0, $xpath->evaluate('count(' . $group . ')'));
        self::assertSame(
            1.0,
            $xpath->evaluate('count(' . $group . '/h2[@id="for-groups"])')
        );
        self::assertSame(2.0, $xpath->evaluate('count(' . $group . '/ol/li)'));
        self::assertGreaterThan(0.0, $xpath->evaluate('count(' . $group . '/p)'));
        self::assertSame(0.0, $xpath->evaluate('count(' . $group . '//dl)'));
        self::assertSame(0.0, $xpath->evaluate('count(//nav//a[@aria-current])'));
        self::assertSame(
            1.0,
            $xpath->evaluate('count(//nav[@aria-label="Menu"]//a[@href="/resources/"])')
        );
        self::assertSame(0.0, $xpath->evaluate('count(//nav[@aria-label="Read"])'));
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
            'https://mastering-the-mundane.com/resources/the-5-ps/'
        );
    }

    public function testExistingMetadataDatesAndSharedStylesheet(): void
    {
        $xpath = $this->page();
        $title = 'The 5 Ps exercise by Josh Bruce'
            . ' | Resources | Mastering the Mundane';
        $description = 'A life design framework to clarify your passions, prohibitions, '
            . 'and career path. Explore the 5 Ps exercise used in team culture '
            . 'and personal coaching.';
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
            'property="og:image"' => 'https://mastering-the-mundane.com/assets/media/poster.png',
            'name="twitter:image"' => 'https://mastering-the-mundane.com/assets/media/poster.png'
            ] as $selector => $value
        ) {
            self::assertSame(
                $value,
                $xpath->evaluate('string(//meta[@' . $selector . ']/@content)')
            );
        }
        $dates = '//main/div[@is="publication-dates"]';
        self::assertSame(2.0, $xpath->evaluate('count(' . $dates . '/p/time)'));
        self::assertSame(
            '2026-05-11',
            $xpath->evaluate('string(' . $dates . '/p[1]/time/@datetime)')
        );
        self::assertSame(
            '2018-09-08',
            $xpath->evaluate('string(' . $dates . '/p[2]/time/@datetime)')
        );
        self::assertSame('Updated:', $xpath->evaluate('string(' . $dates . '/p[1]/b)'));
        self::assertSame('Original:', $xpath->evaluate('string(' . $dates . '/p[2]/b)'));
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
            self::assertTrue($document->loadHTML(FivePsPage::render()));
            return new DOMXPath($document);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }
}
