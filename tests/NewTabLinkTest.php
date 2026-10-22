<?php

declare(strict_types=1);

namespace MTM\Tests;

use Dom\HTMLDocument;
use Dom\XPath;
use MTM\Marketing\UserInterface\NewTabLink;
use PHPUnit\Framework\TestCase;

final class NewTabLinkTest extends TestCase
{
    public function testNewTabLinksKeepTextAndAttributesWithOneAccessibleIcon(): void
    {
        foreach (
            [
                'Apps',
                'Buy on Leanpub',
                'Schedule an introduction (No cost)',
                'A &amp; B',
                'Read <em>more here</em>',
                'Read &#x201c;this&#x201d; ',
                '<img src="/cover.png" alt="Book">'
            ] as $label
        ) {
            $source = '<a href="https://example.com/?a=1&amp;b=2" target="_blank"'
                . ' rel="noopener noreferrer">' . $label . '</a>';
            $html = NewTabLink::decorate($source);
            self::assertSame($html, NewTabLink::decorate($html));
            $document = HTMLDocument::createFromString($html, LIBXML_NOERROR);
            $xpath = new XPath($document);
            $xpath->registerNamespace('h', 'http://www.w3.org/1999/xhtml');
            self::assertSame(
                'https://example.com/?a=1&b=2',
                $xpath->evaluate('string(//h:a/@href)')
            );
            self::assertSame('noopener noreferrer', $xpath->evaluate('string(//h:a/@rel)'));
            self::assertSame(
                1.0,
                $xpath->evaluate('count(//h:a/h:span[@class="new-tab-announcement"])')
            );
            self::assertSame(
                ' (opens in a new tab)',
                $xpath->evaluate('string(//h:span[@class="new-tab-announcement"])')
            );
            self::assertSame(
                1.0,
                $xpath->evaluate(
                    'count(//h:span[@class="new-tab-tail"]'
                        . '/*[local-name()="svg"][@aria-hidden="true"][@focusable="false"])'
                )
            );
            self::assertSame(
                0.0,
                $xpath->evaluate(
                    'count(//h:span[@class="new-tab-tail"]/text()[contains(., " ")])'
                )
            );
            $before = HTMLDocument::createFromString($source, LIBXML_NOERROR);
            $beforeXpath = new XPath($before);
            $beforeXpath->registerNamespace('h', 'http://www.w3.org/1999/xhtml');
            $beforeText = $beforeXpath->evaluate('string(//h:a)');
            self::assertIsString($beforeText);
            self::assertSame(
                $beforeText . ' (opens in a new tab)',
                $xpath->evaluate('string(//h:a)')
            );
        }
    }

    public function testSameTabLinksAndInlineFormattingRemainIntact(): void
    {
        $source = '<p><a href="/books/"><strong>Books</strong></a>'
            . ' and <a href="mailto:hello@example.com">email</a>.</p>';
        self::assertSame($source, NewTabLink::decorate($source));
    }
}
