<?php

declare(strict_types=1);

namespace MTM\Tests;

use Dom\Element;
use Dom\HTMLDocument;
use Dom\XPath;

trait HtmlDocumentAssertions
{
    private static function assertUniqueIds(string $html, string $context): void
    {
        $elements = self::htmlXPath($html)->query('//*[@id]');
        parent::assertNotFalse($elements);
        $seen = [];
        foreach ($elements as $element) {
            parent::assertInstanceOf(Element::class, $element);
            $id = $element->getAttribute('id');
            parent::assertIsString($id);
            $key = 'id:' . $id;
            parent::assertArrayNotHasKey(
                $key,
                $seen,
                $context . ' must not render duplicate id "' . $id . '".'
            );
            $seen[$key] = true;
        }
    }

    private static function assertSinglePageHeading(string $html, string $context): void
    {
        if (preg_match('/<!doctype\s+html\b|<html\b/i', $html) !== 1) {
            return;
        }

        parent::assertSame(
            1,
            self::htmlNodeCount(self::htmlXPath($html), '//*[local-name()="h1"]'),
            $context . ' must render exactly one h1 heading.'
        );
    }

    /**
     * WCAG 2.2 SC 1.3.1 (Level A): heading levels convey structure, so a
     * heading may close any number of levels but open only one at a time.
     */
    private static function assertHeadingLevelsDoNotSkip(string $html, string $context): void
    {
        if (preg_match('/<!doctype\s+html\b|<html\b/i', $html) !== 1) {
            return;
        }

        $headings = self::htmlXPath($html)->query(
            '//*[local-name()="h1" or local-name()="h2" or local-name()="h3"'
                . ' or local-name()="h4" or local-name()="h5" or local-name()="h6"]'
        );
        parent::assertNotFalse($headings);
        $previous = 0;
        foreach ($headings as $heading) {
            parent::assertInstanceOf(Element::class, $heading);
            $level = (int) substr(strtolower($heading->localName), 1);
            parent::assertLessThanOrEqual(
                $previous + 1,
                $level,
                $context . ' skips from h' . $previous . ' to h' . $level . '.'
            );
            $previous = $level;
        }
    }

    private static function htmlXPath(string $html): XPath
    {
        return new XPath(HTMLDocument::createFromString($html, LIBXML_NOERROR));
    }

    private static function htmlNodeCount(XPath $xpath, string $expression): int
    {
        $nodes = $xpath->query($expression);
        parent::assertNotFalse($nodes);
        return $nodes->length;
    }
}
