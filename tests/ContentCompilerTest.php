<?php

declare(strict_types=1);

namespace MTM\Tests;

use InvalidArgumentException;
use MTM\Marketing\ContentCompiler;
use MTM\Marketing\UserInterface\ContentFragment;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ContentCompilerTest extends TestCase
{
    public function testCompilationIsDeterministicAndCommittedFragmentIsFresh(): void
    {
        foreach (['/', '/coaching-and-mentoring/', '/books/'] as $route) {
            $source = file_get_contents(
                dirname(__DIR__) . '/public_content' . $route . 'content.md'
            );
            self::assertIsString($source);
            $compiled = ContentCompiler::compile($source);
            self::assertSame($compiled, ContentCompiler::compile($source));
            self::assertSame($compiled, ContentFragment::forRoute($route));
        }
        $legacyRoutes = [
            '/resources/the-action-loop/',
            '/resources/motivators/',
            '/resources/the-5-ps/'
        ];
        foreach ($legacyRoutes as $route) {
            $source = file_get_contents(
                dirname(__DIR__) . '/public_content' . $route . 'content.md'
            );
            self::assertIsString($source);
            $compiled = ContentCompiler::compile($source);
            self::assertSame($compiled, ContentCompiler::compile($source));
            self::assertSame($compiled, ContentFragment::forRoute($route));
        }
    }

    public function testTrustedInlineHtmlLinksAndAbbreviationsKeepTheirSemantics(): void
    {
        $markdown = "## Heading\n\n**Emphasis** and [services](/services/).\n\n"
            . '<abbr title="United States Dollars">USD</abbr> '
            . '<a href="https://cal.com/example" target="_blank" '
            . 'rel="noopener noreferrer">Book</a>';
        $html = ContentCompiler::compile($markdown);
        self::assertStringContainsString('<h2>Heading</h2>', $html);
        self::assertStringContainsString('<strong>Emphasis</strong>', $html);
        self::assertStringContainsString('<a href="/services/">services</a>', $html);
        self::assertStringContainsString('<abbr title="United States Dollars">USD</abbr>', $html);
        self::assertStringContainsString('target="_blank" rel="noopener noreferrer"', $html);
    }

    public function testMarkdownUnsafeLinksAreNotPublished(): void
    {
        self::assertStringNotContainsString(
            'href="javascript:',
            ContentCompiler::compile('[unsafe](javascript:alert)')
        );
    }

    public function testMarkdownLinksUseExactHostPolicyWithoutSuffixTrust(): void
    {
        foreach (
            [
            '/services/',
            '#details',
            'https://mastering-the-mundane.com/services/',
            'https://MASTERING-THE-MUNDANE.COM/services/'
            ] as $url
        ) {
            $html = ContentCompiler::compile('[Read](' . $url . ')');
            self::assertStringNotContainsString('target=', $html);
            self::assertStringNotContainsString('rel=', $html);
        }
        foreach (
            [
            'https://cal.com/mastering-the-mundane/introduction',
            'https://apps.mastering-the-mundane.com/',
            'https://evilmastering-the-mundane.com/',
            'https://mastering-the-mundane.com.evil.example/',
            '//example.com/path',
            'https://mastering-the-mundane.com@evil.example/'
            ] as $url
        ) {
            $html = ContentCompiler::compile('[Read](' . $url . ')');
            self::assertStringContainsString('target="_blank"', $html);
            self::assertStringContainsString('rel="noopener noreferrer"', $html);
            self::assertStringNotContainsString('nofollow', $html);
        }
    }

    public function testLinkEscapingAndUnsafeSchemesRemainSafe(): void
    {
        $html = ContentCompiler::compile('[A & B](https://example.com/?a=1&b=2)');
        self::assertStringContainsString('A &amp; <span class="new-tab-tail">B', $html);
        self::assertStringContainsString('href="https://example.com/?a=1&amp;b=2"', $html);
        foreach (['javascript:alert(1)', 'data:text/html,test', 'vbscript:msgbox'] as $url) {
            self::assertStringNotContainsString(
                'href="' . $url,
                ContentCompiler::compile('[Unsafe](<' . $url . '>)')
            );
        }
    }

    public function testInlineAbbreviationSyntaxRendersAnAbbreviationNotALink(): void
    {
        $markdown = 'Price in [.United States Dollars](USD); later USD stays plain.';
        $html = ContentCompiler::compile($markdown);
        self::assertSame(
            '<p>Price in <abbr title="United States Dollars">USD</abbr>; later USD stays plain.</p>'
                . "\n",
            $html
        );
        self::assertSame($html, ContentCompiler::compile($markdown));
    }

    public function testCompilerOutputUsesThePublicHostnameAndPortableSourcePath(): void
    {
        $root = dirname(__DIR__);
        $process = proc_open(
            [PHP_BINARY, $root . '/cli/compile-content.php'],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            $root
        );
        self::assertIsResource($process);
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        self::assertSame(0, proc_close($process));
        self::assertSame('', $errors);
        $lines = explode(PHP_EOL, trim($output));
        $htmlLines = array_filter($lines, static fn(string $line): bool =>
            str_ends_with($line, ' -> content.html'));
        $jsonLines = array_values(array_filter($lines, static fn(string $line): bool =>
            str_ends_with($line, ' -> structured-data.json')));
        $expectedJson = [];
        foreach (\MTM\Marketing\UserInterface\PageMetadata::ROUTES as $route) {
            $expectedJson[] = 'compiled (mastering-the-mundane.com): /public_content'
                . $route . 'meta.json -> structured-data.json';
        }
        sort($expectedJson);
        sort($jsonLines);
        self::assertSame($expectedJson, $jsonLines);
        self::assertSame(
            'compiled (mastering-the-mundane.com): '
                . '/public_content/books/content.md -> content.html' . PHP_EOL
                . 'compiled (mastering-the-mundane.com): '
                . '/public_content/books/time-mastering-the-mundane/content.md -> content.html'
                . PHP_EOL
                . 'compiled (mastering-the-mundane.com): '
                . '/public_content/coaching-and-mentoring/content.md -> content.html' . PHP_EOL
                . 'compiled (mastering-the-mundane.com): '
                . '/public_content/content.md -> content.html' . PHP_EOL
                . 'compiled (mastering-the-mundane.com): '
                . '/public_content/resources/published.json -> content.html' . PHP_EOL
                . 'compiled (mastering-the-mundane.com): '
                . '/public_content/resources/motivators/content.md -> content.html'
                . PHP_EOL
                . 'compiled (mastering-the-mundane.com): '
                . '/public_content/resources/the-5-ps/content.md -> content.html'
                . PHP_EOL
                . 'compiled (mastering-the-mundane.com): '
                . '/public_content/resources/the-action-loop/content.md -> content.html'
                . PHP_EOL
                . 'compiled (mastering-the-mundane.com): '
                . '/public_content/services/content.md -> content.html'
                . PHP_EOL,
            implode(PHP_EOL, $htmlLines) . PHP_EOL
        );
        self::assertStringNotContainsString($root, $output);
    }

    public function testMissingArtifactFailsExplicitly(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('run composer compile-content');
        ContentFragment::forRoute('/not-published/');
    }

    public function testConvertedContentHasNoPublicSourceCopies(): void
    {
        foreach (['the-action-loop', 'motivators', 'the-5-ps'] as $slug) {
            foreach (['content.md', 'content.html', 'meta.json'] as $file) {
                self::assertFileExists(
                    dirname(__DIR__) . '/public_content/resources/' . $slug . '/' . $file
                );
                self::assertFileDoesNotExist(
                    dirname(__DIR__) . '/site-root/public/resources/' . $slug . '/' . $file
                );
            }
        }
    }

    public function testDescriptionListsPreserveTermsDefinitionsAndInlineSemantics(): void
    {
        self::assertSame(
            "<dl>\n<dt>Term</dt>\n<dd>A <em>meaning</em>.</dd>\n</dl>\n",
            ContentCompiler::compile("Term\n: A *meaning*.")
        );
    }

    public function testContentRoutesCannotEscapeTheContentDirectory(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ContentFragment::forRoute('/../site-root/');
    }
}
