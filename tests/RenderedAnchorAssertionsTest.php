<?php

declare(strict_types=1);

namespace MTM\Tests;

use DOMDocument;
use DOMXPath;
use MTM\Marketing\ContentCompiler;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RenderedAnchorAssertionsTest extends TestCase
{
    public function testRenderedRawAndMarkdownLinksAreClassifiedByDestination(): void
    {
        $markdown = '';
        foreach (
            [
                '/', '../services/', '#main', '?page=2',
                'http://mastering-the-mundane.com/',
                'https://MASTERING-THE-MUNDANE.COM/',
                '//mastering-the-mundane.com/'
            ] as $href
        ) {
            $markdown .= '<a href="' . $href . '" target="_self">Internal</a>' . "\n\n";
        }
        foreach (
            [
                'https://apps.mastering-the-mundane.com/',
                'https://evilmastering-the-mundane.com/',
                'https://mastering-the-mundane.com.evil.example/',
                'https://mastering-the-mundane.com@evil.example/',
                '//example.com/'
            ] as $href
        ) {
            $markdown .= '<a href="' . $href . '" target="_blank" '
                . 'rel="external noreferrer NOOPENER">External</a>' . "\n\n";
        }
        $markdown .= "[External Markdown](https://example.com/)\n\n"
            . "[Internal Markdown](/services/)\n\n"
            . '<a href="mailto:hello@example.com">Email</a>';
        RenderedAnchorAssertions::assertHttpHostPolicy(
            $this->renderedFixture($markdown),
            'https://mastering-the-mundane.com/coaching-and-mentoring/'
        );
    }

    #[DataProvider('violations')]
    public function testUnsafeRawAnchorsFailAfterActualCompilation(
        string $anchor,
        string $message
    ): void {
        $rendered = $this->renderedFixture($anchor);
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage($message);
        RenderedAnchorAssertions::assertHttpHostPolicy(
            $rendered,
            'https://mastering-the-mundane.com/coaching-and-mentoring/'
        );
    }

    /** @return array<string, array{string, string}> */
    public static function violations(): array
    {
        return [
            'missing target' => [
                '<a href="https://example.com/" rel="noopener noreferrer">External</a>',
                'External anchor must open a new tab'
            ],
            'missing noopener' => [
                '<a href="https://example.com/" target="_blank" rel="noreferrer">External</a>',
                'External anchor needs noopener'
            ],
            'missing noreferrer' => [
                '<a href="https://example.com/" target="_blank" rel="noopener">External</a>',
                'External anchor needs noreferrer'
            ],
            'internal new tab' => [
                '<a href="//MASTERING-THE-MUNDANE.COM/" target="_blank">Internal</a>',
                'Internal anchor must stay in the same tab'
            ],
            'external base changes relative destination' => [
                '<base href="https://example.com/"><a href="?page=2">External</a>',
                'External anchor must open a new tab'
            ],
            'backslash authority' => [
                '<a href="https:&#92;&#92;evil.example/">External</a>',
                'External anchor must open a new tab'
            ],
            'backslash terminates host before userinfo' => [
                '<a href="https://evil.example&#92;@mastering-the-mundane.com/">External</a>',
                'External anchor must open a new tab'
            ],
            'backslash base changes relative destination' => [
                '<base href="https:&#92;&#92;evil.example/path/"><a href="../">External</a>',
                'External anchor must open a new tab'
            ],
            'malformed HTTP anchor' => [
                '<a href="https://[invalid/">Invalid</a>',
                'Invalid rendered URL'
            ],
            'malformed HTTP base' => [
                '<base href="https://[invalid/"><a href="/">Invalid</a>',
                'Invalid rendered URL'
            ]
        ];
    }

    public function testRelativeBaseAndBrowserNormalizedBackslashesRemainInternal(): void
    {
        $rendered = $this->renderedFixture(
            '<base href="../reading/"><a href="?page=2">Query</a>'
                . '<a href="#main">Fragment</a><a href="../services/">Relative</a>'
                . '<a href="https://mastering-the-mundane.com&#92;@evil.example/">Internal</a>'
        );
        RenderedAnchorAssertions::assertHttpHostPolicy(
            $rendered,
            'https://mastering-the-mundane.com/coaching-and-mentoring/'
        );
    }

    private function renderedFixture(string $markdown): DOMXPath
    {
        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        try {
            self::assertTrue($document->loadHTML(ContentCompiler::compile($markdown)));
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        return new DOMXPath($document);
    }
}
