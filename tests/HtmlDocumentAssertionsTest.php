<?php

declare(strict_types=1);

namespace MTM\Tests;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class HtmlDocumentAssertionsTest extends TestCase
{
    use HtmlDocumentAssertions;

    /** @return iterable<string, array{string}> */
    public static function validPageProvider(): iterable
    {
        yield 'shared navigation' => [
            '<!doctype html><html><body><header><h1>App</h1></header>'
                . '<main><h2>Task</h2></main></body></html>'
        ];
        yield 'hidden navigation' => [
            '<html><body><main><h1>Holding page</h1></main></body></html>'
        ];
        yield 'comment is not a heading' => [
            '<!DOCTYPE HTML><html><body><!-- <h1>Not rendered</h1> -->'
                . '<h1>Page</h1></body></html>'
        ];
    }

    #[Test]
    #[DataProvider('validPageProvider')]
    public function accepts_one_heading_in_a_full_document(string $html): void
    {
        self::assertSinglePageHeading($html, 'GET /example/');
    }

    /** @return iterable<string, array{string}> */
    public static function invalidPageProvider(): iterable
    {
        yield 'missing heading' => ['<!doctype html><html><body><h2>Task</h2></body></html>'];
        yield 'duplicate heading' => [
            '<!doctype html><html><body><header><h1>App</h1></header>'
                . '<main><h1>Task</h1></main></body></html>'
        ];
    }

    #[Test]
    #[DataProvider('invalidPageProvider')]
    public function rejects_missing_or_duplicate_page_headings(string $html): void
    {
        parent::expectException(AssertionFailedError::class);
        parent::expectExceptionMessage('GET /example/ must render exactly one h1 heading.');
        self::assertSinglePageHeading($html, 'GET /example/');
    }

    #[Test]
    public function does_not_treat_redirect_bodies_fragments_or_json_as_pages(): void
    {
        parent::expectNotToPerformAssertions();
        $bodies = ['', '<p>Invalid request.</p>', '{"success":true}', '<h3>Fragment</h3>'];
        foreach ($bodies as $body) {
            self::assertSinglePageHeading($body, 'POST /example/');
            self::assertHeadingLevelsDoNotSkip($body, 'POST /example/');
        }
    }

    #[Test]
    public function accepts_headings_that_open_one_level_and_close_any(): void
    {
        self::assertHeadingLevelsDoNotSkip(
            '<!doctype html><html><body><h1>App</h1><h2>Section</h2><h3>Card</h3>'
                . '<h4>Detail</h4><h2>Next section</h2><h3>Card</h3></body></html>',
            'GET /example/'
        );
    }

    /** @return iterable<string, array{string, string}> */
    public static function skippedLevelProvider(): iterable
    {
        yield 'first heading below h1' => [
            '<!doctype html><html><body><h2>Section</h2></body></html>',
            'GET /example/ skips from h0 to h2.'
        ];
        yield 'card heading without section heading' => [
            '<!doctype html><html><body><h1>App</h1><h3>Card</h3></body></html>',
            'GET /example/ skips from h1 to h3.'
        ];
        yield 'skip after closing levels' => [
            '<!doctype html><html><body><h1>App</h1><h2>Section</h2><h3>Card</h3>'
                . '<h2>Next</h2><h4>Detail</h4></body></html>',
            'GET /example/ skips from h2 to h4.'
        ];
    }

    #[Test]
    #[DataProvider('skippedLevelProvider')]
    public function rejects_skipped_heading_levels(string $html, string $message): void
    {
        parent::expectException(AssertionFailedError::class);
        parent::expectExceptionMessage($message);
        self::assertHeadingLevelsDoNotSkip($html, 'GET /example/');
    }

    /** @return iterable<string, array{string}> */
    public static function uniqueIdProvider(): iterable
    {
        yield 'no ids' => ['<main><h1>Page</h1></main>'];
        yield 'unique ids and repeated references' => [
            '<label for="name">Name</label><input id="name">'
                . '<p id="hint">Hint</p><a href="#name">Back</a>'
        ];
        yield 'case sensitive' => ['<p id="Name"></p><p id="name"></p>'];
        yield 'numeric strings stay distinct' => ['<p id="0"></p><p id="00"></p>'];
        yield 'comments and escaped examples are not elements' => [
            '<p id="example"></p><!-- <p id="example"></p> -->'
                . '<code>&lt;p id="example"&gt;&lt;/p&gt;</code>'
        ];
        yield 'script text is not markup' => [
            '<p id="example"></p><script>const sample = \'<p id="example"></p>\';</script>'
        ];
    }

    #[Test]
    #[DataProvider('uniqueIdProvider')]
    public function accepts_unique_ids(string $html): void
    {
        self::assertUniqueIds($html, 'GET /example/');
    }

    /** @return iterable<string, array{string, string}> */
    public static function duplicateIdProvider(): iterable
    {
        yield 'siblings' => ['<p id="name"></p><input id="name">', 'name'];
        yield 'ancestor and descendant' => ['<main id="main"><p id="main"></p></main>', 'main'];
        yield 'entity decoded values' => ['<p id="a&amp;b"></p><p id="a&#38;b"></p>', 'a&b'];
        yield 'numeric zero' => ['<p id="0"></p><p id="0"></p>', '0'];
        yield 'empty ids' => ['<p id=""></p><p id=""></p>', ''];
        yield 'SVG and HTML share ids' => [
            '<svg><title id="label">Icon</title></svg><p id="label">Label</p>', 'label'
        ];
        yield 'full document' => [
            '<!doctype html><html><body><header id="main"></header>'
                . '<main id="main"><h1>Page</h1></main></body></html>', 'main'
        ];
    }

    #[Test]
    #[DataProvider('duplicateIdProvider')]
    public function rejects_duplicate_ids_with_request_context(string $html, string $id): void
    {
        parent::expectException(AssertionFailedError::class);
        parent::expectExceptionMessage('GET /example/ must not render duplicate id "' . $id . '".');
        self::assertUniqueIds($html, 'GET /example/');
    }
}
