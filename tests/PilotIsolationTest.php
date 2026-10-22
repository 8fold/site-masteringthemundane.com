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
            '/index.php',
            '/books/index.php',
            '/books/time-mastering-the-mundane/index.php',
            '/services/index.php',
            '/tools-and-resources/index.php',
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
        self::assertStringNotContainsString('@font-face', $css);
        self::assertStringNotContainsString('url(', $css);
        self::assertStringContainsString(':focus-visible', $css);
        self::assertStringContainsString('[is="skip-link"]:focus', $css);
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
