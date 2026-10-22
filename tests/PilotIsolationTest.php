<?php

declare(strict_types=1);

namespace MTM\Tests;

use PHPUnit\Framework\TestCase;

final class PilotIsolationTest extends TestCase
{
    public function testLegacyRoutesStillRenderTheirOriginalStylesheetWithoutThePilotShell(): void
    {
        $root = dirname(__DIR__) . '/site-root/public';
        foreach (
            [
            '/books/index.php',
            '/books/time-mastering-the-mundane/index.php',
            ] as $route
        ) {
            $html = $this->renderLegacyRoute($root . $route);
            self::assertStringStartsWith('<!doctype html>', $html, $route);
            self::assertStringContainsString('/css/main.css?v=1.1', $html, $route);
            self::assertStringNotContainsString('coaching.css', $html, $route);
            self::assertStringNotContainsString('reading.css', $html, $route);
            self::assertStringNotContainsString('is="marketing-header"', $html, $route);
        }
    }

    public function testPilotStylesRemainLocalAndIncludeFocusAndSkipLinkStates(): void
    {
        $root = dirname(__DIR__) . '/site-root/public';
        $css = file_get_contents($root . '/css/reading.css');
        self::assertIsString($css);
        self::assertStringNotContainsString('@import', $css);
        self::assertSame(12, substr_count($css, '@font-face'));
        preg_match_all('/@font-face\s*\{(.*?)\}/s', $css, $faces);
        self::assertCount(12, $faces[1]);
        $localNextFaces = [
            'normal:400' => 'Regular',
            'italic:400' => 'RegularItalic',
            'normal:500' => 'Medium',
            'italic:500' => 'MediumItalic',
            'normal:700' => 'Bold',
            'italic:700' => 'BoldItalic'
        ];
        $localOriginalFaces = [
            'normal:400' => 'Regular',
            'italic:400' => 'Italic',
            'normal:700' => 'Bold',
            'italic:700' => 'BoldItalic'
        ];
        $faceStyles = [];
        foreach ($faces[1] as $face) {
            preg_match('/font-style:\s*(normal|italic);/', $face, $style);
            preg_match('/font-weight:\s*(400|500|700);/', $face, $weight);
            self::assertNotEmpty($style);
            self::assertNotEmpty($weight);
            $descriptor = $style[1] . ':' . $weight[1];
            $faceStyles[$descriptor] = ($faceStyles[$descriptor] ?? 0) + 1;
            $source = 'src: local("AtkinsonHyperlegibleNext-'
                . $localNextFaces[$descriptor] . '"), ';
            if (isset($localOriginalFaces[$descriptor])) {
                $source .= 'local("AtkinsonHyperlegible-'
                    . $localOriginalFaces[$descriptor] . '"), ';
            }
            self::assertStringContainsString($source . 'url("/fonts/', $face);
            self::assertStringContainsString(
                'font-family: "Atkinson Hyperlegible Next Hosted";',
                $face
            );
            $fileStyle = $style[1] === 'normal' ? 'Normal' : 'Italic';
            self::assertStringContainsString('Next-v7-' . $fileStyle . '-', $face);
        }
        self::assertSame(
            [
                'normal:400' => 2,
                'italic:400' => 2,
                'normal:500' => 2,
                'italic:500' => 2,
                'normal:700' => 2,
                'italic:700' => 2
            ],
            $faceStyles
        );
        self::assertStringContainsString(
            '--font-body: "Atkinson Hyperlegible Next Hosted", -apple-system,',
            $css
        );
        self::assertStringNotContainsString('--font-body: "Atkinson Hyperlegible Next",', $css);
        self::assertStringNotContainsString('--font-body: "Atkinson Hyperlegible",', $css);
        self::assertSame(12, substr_count($css, 'font-display: swap'));
        self::assertStringNotContainsString('AtkinsonHyperlegible-v12-', $css);
        self::assertStringNotContainsString('https://', $css);
        $fontFiles = glob($root . '/fonts/AtkinsonHyperlegibleNext-v7-*.woff2');
        self::assertIsArray($fontFiles);
        self::assertCount(4, $fontFiles);
        foreach ($fontFiles as $fontFile) {
            $hash = hash_file('sha256', $fontFile);
            self::assertIsString($hash);
            self::assertSame(
                3,
                substr_count($css, '/fonts/' . basename($fontFile) . '?v=' . $hash)
            );
        }
        self::assertFileExists($root . '/fonts/AtkinsonHyperlegibleNext-OFL.txt');
        self::assertStringContainsString(':focus-visible', $css);
        self::assertStringContainsString('[is="skip-link"]:focus', $css);
    }

    public function testFontCachePolicyIsLimitedToVersionedWoff2Files(): void
    {
        $config = file_get_contents(dirname(__DIR__) . '/site-root/public/.htaccess');
        self::assertIsString($config);
        self::assertStringContainsString('m#\.woff2$#', $config);
        self::assertStringContainsString('v=[0-9a-f]{64}(&|$)', $config);
        self::assertStringContainsString(
            'Header set Cache-Control "public, max-age=31536000, immutable"',
            $config
        );
        self::assertStringContainsString('Header set Cache-Control "no-cache"', $config);
    }

    public function testRootProseListsShareParagraphSpacingWithoutChangingInternalGaps(): void
    {
        $css = file_get_contents(dirname(__DIR__) . '/site-root/public/css/reading.css');
        self::assertIsString($css);
        $normalizedCss = preg_replace('/\s+/', ' ', $css);
        self::assertIsString($normalizedCss);
        self::assertStringContainsString(
            'main > :is(ul, ol, dl):not([is]):not([role]):not(:has(> li[is])):not(nav *)',
            $normalizedCss
        );
        self::assertStringContainsString(
            'main > section[is="group-application"] > :is(ul, ol, dl)'
                . ':not([is]):not([role]):not(:has(> li[is])):not(nav *)',
            $normalizedCss
        );
        self::assertStringContainsString('margin-block: 0 var(--space-md);', $css);
        self::assertStringContainsString('> li + li,', $css);
        self::assertStringContainsString('> dd + dt {', $css);
        self::assertStringContainsString('margin-block-start: var(--space-xs);', $css);
        self::assertStringContainsString('nav ul {', $css);
    }

    public function testReadingLinkUnderlinesEmphasizeHoverAndCurrentPageStates(): void
    {
        $css = file_get_contents(dirname(__DIR__) . '/site-root/public/css/reading.css');
        self::assertIsString($css);
        $normalizedCss = preg_replace('/\s+/', ' ', $css);
        self::assertIsString($normalizedCss);
        self::assertStringContainsString(
            'a, a:visited, a:active { color: var(--color-ink); text-decoration: underline; '
                . 'text-decoration-color: var(--color-accent); text-decoration-thickness: 2px; '
                . 'text-underline-offset: var(--space-4xs); }',
            $normalizedCss
        );
        self::assertStringContainsString(
            'a:hover { text-decoration-thickness: 3px; }',
            $normalizedCss
        );
        self::assertStringContainsString(
            'nav a[aria-current="page"] { font-weight: var(--font-weight-bold); '
                . 'text-decoration-style: double; text-decoration-thickness: 1px; }',
            $normalizedCss
        );
        self::assertStringContainsString(
            'nav a[aria-current="page"]:hover { text-decoration-thickness: 2px; }',
            $normalizedCss
        );
        self::assertStringContainsString(
            'a:focus-visible { outline: 3px solid var(--color-information); '
                . 'outline-offset: var(--space-3xs); border-radius: var(--space-4xs); }',
            $normalizedCss
        );
        self::assertStringContainsString('text-decoration-color: LinkText;', $css);
        self::assertStringContainsString(
            'nav button { min-height: 44px; padding-block: var(--space-sm); '
                . 'padding-inline: var(--space-2xs); border: 0; background: transparent; '
                . 'font: inherit; color: var(--color-ink); '
                . 'text-decoration: underline 2px var(--color-accent); '
                . 'text-underline-offset: var(--space-4xs); cursor: pointer; }',
            $normalizedCss
        );
        self::assertStringContainsString(
            'nav button:hover { text-decoration-thickness: 3px; }',
            $normalizedCss
        );
    }

    public function testReadingPagesDoNotPreloadFonts(): void
    {
        $html = $this->renderLegacyRoute(
            dirname(__DIR__) . '/site-root/public/coaching-and-mentoring/index.php'
        );
        self::assertStringNotContainsString('rel="preload"', $html);
    }

    private function renderLegacyRoute(string $file): string
    {
        $process = proc_open(
            [
                PHP_BINARY,
                '-r',
                '$_SERVER["REQUEST_URI"] = $argv[1]; require $argv[2];',
                str_replace(dirname(__DIR__) . '/site-root/public', '', dirname($file)) . '/',
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
        self::assertSame(
            0,
            proc_close($process),
            $errors === false ? 'Cannot read stderr' : $errors
        );
        self::assertSame('', $errors);
        self::assertIsString($html);
        return $html;
    }
}
