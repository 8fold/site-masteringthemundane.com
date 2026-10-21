<?php

declare(strict_types=1);

namespace MTM\Tests;

use DOMElement;
use DOMXPath;
use PHPUnit\Framework\Assert;
use Uri\WhatWg\Url;

final class RenderedAnchorAssertions
{
    public static function assertHttpHostPolicy(DOMXPath $xpath, string $documentUrl): void
    {
        $document = self::resolve($documentUrl);
        $internalHost = $document->getAsciiHost();
        Assert::assertIsString($internalHost);
        $baseUrl = $document;
        $bases = $xpath->query('//base[@href]');
        Assert::assertNotFalse($bases);
        $base = $bases->item(0);
        if ($base instanceof DOMElement) {
            $baseUrl = self::resolve($base->getAttribute('href'), $document);
        }
        $anchors = $xpath->query('//a[@href]');
        Assert::assertNotFalse($anchors);
        foreach ($anchors as $anchor) {
            Assert::assertInstanceOf(DOMElement::class, $anchor);
            $href = $anchor->getAttribute('href');
            $url = self::resolve($href, $baseUrl);
            if (!in_array($url->getScheme(), ['http', 'https'], true)) {
                continue;
            }
            $host = $url->getAsciiHost();
            Assert::assertIsString($host, 'Invalid HTTP(S) anchor: ' . $href);
            $target = $anchor->getAttribute('target');
            if (strcasecmp($host, $internalHost) === 0) {
                Assert::assertContains(
                    strtolower($target),
                    ['', '_self'],
                    'Internal anchor must stay in the same tab: ' . $href
                );
                continue;
            }
            Assert::assertSame('_blank', $target, 'External anchor must open a new tab: ' . $href);
            $tokens = preg_split('/\s+/', strtolower(trim($anchor->getAttribute('rel'))));
            Assert::assertIsArray($tokens);
            foreach (['noopener', 'noreferrer'] as $required) {
                Assert::assertContains(
                    $required,
                    $tokens,
                    'External anchor needs ' . $required . ': ' . $href
                );
            }
        }
    }

    private static function resolve(string $href, ?Url $baseUrl = null): Url
    {
        $url = Url::parse($href, $baseUrl);
        Assert::assertInstanceOf(Url::class, $url, 'Invalid rendered URL: ' . $href);
        return $url;
    }
}
