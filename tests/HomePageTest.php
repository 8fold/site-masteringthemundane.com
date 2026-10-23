<?php

declare(strict_types=1);

namespace MTM\Tests;

use DOMDocument;
use DOMXPath;
use MTM\Marketing\UserInterface\HomePage;
use PHPUnit\Framework\TestCase;

final class HomePageTest extends TestCase
{
    public function testFourDomainsHaveHeadingsAndSupportDestinations(): void
    {
        $previous = libxml_use_internal_errors(true);
        try {
            $document = new DOMDocument();
            self::assertTrue($document->loadHTML(HomePage::render()));
            $xpath = new DOMXPath($document);
            self::assertSame(1.0, $xpath->evaluate('count(//h1)'));
            self::assertSame(4.0, $xpath->evaluate('count(//main/section)'));
            foreach (['time', 'people', 'space', 'money'] as $id) {
                $section = '//main/section[@aria-labelledby="' . $id . '"]';
                self::assertSame(
                    ucfirst($id),
                    $xpath->evaluate('string(' . $section . '/h2[@id="' . $id . '"])')
                );
                foreach (['coaching-and-mentoring', 'services'] as $destination) {
                    self::assertSame(
                        1.0,
                        $xpath->evaluate(
                            'count(' . $section . '//a[@href="/' . $destination . '/"])'
                        )
                    );
                }
            }
            self::assertSame(0.0, $xpath->evaluate('count(//main//h3|//form|//iframe)'));
            self::assertSame(1.0, $xpath->evaluate('count(//nav//a[@aria-current])'));
            self::assertSame(
                '/',
                $xpath->evaluate('string(//nav//a[@aria-current="page"]/@href)')
            );
            RenderedAnchorAssertions::assertHttpHostPolicy(
                $xpath,
                'https://mastering-the-mundane.com/'
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    public function testPhysicalHomeEntryUsesSharedPage(): void
    {
        ob_start();
        require dirname(__DIR__) . '/public/index.php';
        $html = ob_get_clean();
        self::assertSame(HomePage::render(), $html);
    }
}
