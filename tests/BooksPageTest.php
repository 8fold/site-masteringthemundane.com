<?php

declare(strict_types=1);

namespace MTM\Tests;

use DOMDocument;
use DOMXPath;
use MTM\Marketing\UserInterface\BooksPage;
use MTM\Marketing\UserInterface\ContentFragment;
use PHPUnit\Framework\TestCase;

final class BooksPageTest extends TestCase
{
    public function testCatalogSeparatesAvailableBooksFromCurrentWorkWithoutImages(): void
    {
        $previous = libxml_use_internal_errors(true);
        try {
            $document = new DOMDocument();
            self::assertTrue($document->loadHTML(BooksPage::render()));
            $xpath = new DOMXPath($document);
            self::assertSame('Books', $xpath->evaluate('string(//header/h1)'));
            self::assertSame(
                'Books | Mastering the Mundane',
                $xpath->evaluate('string(//title)')
            );
            self::assertSame(1.0, $xpath->evaluate('count(//h1)'));
            self::assertSame('Available books', $xpath->evaluate('string(//main/h2[1])'));
            self::assertSame('Currently working on', $xpath->evaluate('string(//main/h2[2])'));
            foreach (
                [
                    'Time: Mastering the Mundane',
                    'Triumph over Time, 1st Edition',
                    'Money: Mastering the Mundane'
                ] as $index => $title
            ) {
                self::assertSame(
                    $title,
                    $xpath->evaluate('string((//main/section/h3)[' . ($index + 1) . '])')
                );
            }
            self::assertSame(
                'Money: Mastering the Mundane',
                $xpath->evaluate('string(//main/h2[2]/following-sibling::section[1]/h3)')
            );
            self::assertSame(
                0.0,
                $xpath->evaluate('count(//main/h2[2]/following-sibling::*//a)')
            );
            foreach (
                ['master-the-mundane', 'triumph-over-time-principle-based-productivity'] as $slug
            ) {
                self::assertSame(
                    1.0,
                    $xpath->evaluate('count(//main//a[@href="https://leanpub.com/' . $slug . '"])')
                );
            }
            self::assertSame(
                1.0,
                $xpath->evaluate('count(//main//a[@href="/books/time-mastering-the-mundane/"])')
            );
            self::assertSame(1.0, $xpath->evaluate('count(//nav//a[@aria-current])'));
            self::assertSame(
                '/books/',
                $xpath->evaluate('string(//nav//a[@aria-current="page"]/@href)')
            );
            self::assertSame(0.0, $xpath->evaluate('count(//body//img|//form|//iframe)'));
            self::assertSame(3.0, $xpath->evaluate('count(//main/section[h3])'));
            self::assertSame(0.0, $xpath->evaluate('count(//main/h3)'));
            foreach (['time', 'triumph-over-time', 'money'] as $id) {
                self::assertSame(
                    1.0,
                    $xpath->evaluate(
                        'count(//main/section[@aria-labelledby="' . $id . '"]'
                            . '/*[1][self::h3][@id="' . $id . '"])'
                    )
                );
            }
            self::assertStringContainsString(
                ContentFragment::forRoute('/books/'),
                BooksPage::render()
            );
            RenderedAnchorAssertions::assertHttpHostPolicy(
                $xpath,
                'https://mastering-the-mundane.com/books/'
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    public function testPhysicalCatalogRouteDoesNotLoadTheMarkdownConverter(): void
    {
        $file = dirname(__DIR__) . '/public/books/index.php';
        $process = proc_open(
            [
                PHP_BINARY,
                '-r',
                'require $argv[1];'
                    . ' if (class_exists("Eightfold\\\\Markdown\\\\Markdown", false)) {'
                    . ' throw new \RuntimeException("Runtime loaded the Markdown converter."); }',
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
        self::assertSame(BooksPage::render(), $html);
    }
}
