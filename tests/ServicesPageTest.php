<?php

declare(strict_types=1);

namespace MTM\Tests;

use DOMDocument;
use DOMXPath;
use MTM\Marketing\UserInterface\ServicesPage;
use PHPUnit\Framework\TestCase;

final class ServicesPageTest extends TestCase
{
    public function testPhysicalRouteUsesCompiledContentWithoutRuntimeMarkdown(): void
    {
        $file = dirname(__DIR__) . '/site-root/public/services/index.php';
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
        self::assertSame(ServicesPage::render(), $html);
    }

    public function testServiceStructureNavigationAndLinks(): void
    {
        $previous = libxml_use_internal_errors(true);
        try {
            $document = new DOMDocument();
            self::assertTrue($document->loadHTML(ServicesPage::render()));
            $xpath = new DOMXPath($document);
            self::assertSame(1.0, $xpath->evaluate('count(//h1)'));
            self::assertSame('Services', $xpath->evaluate('string(//header/h1)'));
            self::assertSame(
                1.0,
                $xpath->evaluate('count(//main[@id="main"][@tabindex="-1"])')
            );
            self::assertSame(1.0, $xpath->evaluate('count(//a[@href="#main"])'));
            self::assertSame(2.0, $xpath->evaluate('count(//main/dl/dt)'));
            self::assertSame(2.0, $xpath->evaluate('count(//main/dl/dd)'));
            self::assertSame(
                0.0,
                $xpath->evaluate('count(//main/dl/dt[not(following-sibling::*[1][self::dd])])')
            );
            self::assertSame(2.0, $xpath->evaluate('count(//main/dl/dd/ul)'));
            self::assertSame(0.0, $xpath->evaluate('count(//main//h1|//main/article)'));
            $current = '//nav[@aria-label="Menu"]//a[@aria-current="page"]';
            self::assertSame(1.0, $xpath->evaluate('count(//nav//a[@aria-current])'));
            self::assertSame('/services/', $xpath->evaluate('string(' . $current . '/@href)'));
            self::assertSame(
                'Coaching',
                $xpath->evaluate('string(' . $current . '/../preceding-sibling::li[1]/a)')
            );
            self::assertSame(
                1.0,
                $xpath->evaluate('count(//main//a[@href="/coaching-and-mentoring/"])')
            );
            self::assertSame(
                1.0,
                $xpath->evaluate(
                    'count(//main//a[@href="https://cal.com/mastering-the-mundane/introduction"])'
                )
            );
            self::assertSame(0.0, $xpath->evaluate('count(//form|//iframe|//script[@src])'));
            RenderedAnchorAssertions::assertHttpHostPolicy(
                $xpath,
                'https://mastering-the-mundane.com/services/'
            );
            self::assertSame(
                'Services | Mastering the Mundane',
                $xpath->evaluate('string(//title)')
            );
            $description = $xpath->evaluate('string(//meta[@name="description"]/@content)');
            self::assertNotSame('', $description);
            foreach (['og:description', 'twitter:description'] as $name) {
                self::assertSame(
                    $description,
                    $xpath->evaluate(
                        'string(//meta[@property="' . $name
                            . '" or @name="' . $name . '"]/@content)'
                    )
                );
            }
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }
}
