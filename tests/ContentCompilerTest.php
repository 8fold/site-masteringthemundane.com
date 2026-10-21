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
        $source = file_get_contents(
            dirname(__DIR__) . '/public_content/coaching-and-mentoring/content.md'
        );
        self::assertIsString($source);
        $compiled = ContentCompiler::compile($source);
        self::assertSame($compiled, ContentCompiler::compile($source));
        self::assertSame($compiled, ContentFragment::forRoute('/coaching-and-mentoring/'));
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
        self::assertStringContainsString('A &amp; B</a>', $html);
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
        self::assertSame(
            'compiled (mastering-the-mundane.com): '
                . '/public_content/coaching-and-mentoring/content.md -> content.html' . PHP_EOL,
            $output
        );
        self::assertStringNotContainsString($root, $output);
    }

    public function testMissingArtifactFailsExplicitly(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('run composer compile-content');
        ContentFragment::forRoute('/not-published/');
    }

    public function testContentRoutesCannotEscapeTheContentDirectory(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ContentFragment::forRoute('/../site-root/');
    }
}
