<?php

declare(strict_types=1);

namespace MTM\Tests;

use DOMDocument;
use DOMXPath;
use MTM\ContentCompiler;
use MTM\Marketing\UserInterface\TimeBookPage;
use PHPUnit\Framework\TestCase;

final class TimeBookPageTest extends TestCase
{
    public function testDetailPreservesBookContentAndRetailersInSharedShell(): void
    {
        $previous = libxml_use_internal_errors(true);
        try {
            $document = new DOMDocument();
            self::assertTrue($document->loadHTML(TimeBookPage::render()));
            $xpath = new DOMXPath($document);
            self::assertSame(1.0, $xpath->evaluate('count(//h1)'));
            self::assertSame(
                'Time: Mastering the Mundane',
                $xpath->evaluate('string(//header/h1)')
            );
            self::assertSame(
                'Time: Mastering the Mundane | Books | Mastering the Mundane',
                $xpath->evaluate('string(//title)')
            );
            self::assertSame(0.0, $xpath->evaluate('count(//main//img|//main//figure)'));
            self::assertSame(
                'Part of the Mastering the Mundane set of self-management books.',
                $xpath->evaluate('string(//main/section/blockquote/p)')
            );
            self::assertSame(1.0, $xpath->evaluate('count(//main//blockquote)'));
            self::assertSame(4.0, $xpath->evaluate('count(//main//a)'));
            foreach (
                [
                    'https://www.scribl.com/books/EEDEB8/Time',
                    'https://leanpub.com/master-the-mundane',
                    'https://books.apple.com/us/book/time/id6504804280',
                    'https://www.amazon.com/Time-Mastering-Mundane-Josh-Bruce-ebook/dp/B0D7ZYHLN2'
                ] as $url
            ) {
                self::assertSame(
                    1.0,
                    $xpath->evaluate('count(//main//a[@href="' . $url . '"])')
                );
            }
            self::assertSame(
                'Digital Rights Management',
                $xpath->evaluate('string(//main//abbr/@title)')
            );
            RenderedAnchorAssertions::assertHttpHostPolicy(
                $xpath,
                'https://mastering-the-mundane.com/books/time-mastering-the-mundane/'
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        $directory = dirname(__DIR__) . '/public_content/books/time-mastering-the-mundane/';
        $source = file_get_contents($directory . 'content.md');
        self::assertIsString($source);
        self::assertSame(
            file_get_contents($directory . 'content.html'),
            ContentCompiler::compile($source)
        );
    }
}
