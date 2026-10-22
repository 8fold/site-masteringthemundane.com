<?php

declare(strict_types=1);

namespace MTM\Tests;

use DOMDocument;
use DOMXPath;
use MatthiasMullie\Minify\CSS;
use MTM\Marketing\UserInterface\AssetUrl;
use MTM\Marketing\UserInterface\HomePage;
use PHPUnit\Framework\TestCase;

final class AssetLayoutTest extends TestCase
{
    public function testGeneratedStylesheetsAreFreshAndKeepLocalFontUrls(): void
    {
        $root = dirname(__DIR__) . '/public';
        foreach (['main', 'reading'] as $name) {
            $source = $root . '/assets/css/' . $name . '.css';
            $target = $root . '/assets/css/' . $name . '.min.css';
            $generated = (new CSS($source))->minify();
            self::assertSame($generated, file_get_contents($target));
            preg_match_all('~url\(["\']?(/[^)"\']+)~', $generated, $matches);
            self::assertNotEmpty($matches[1]);
            foreach ($matches[1] as $url) {
                $path = explode('?', $url)[0];
                self::assertFileExists($root . $path);
                self::assertDoesNotMatchRegularExpression('~^/(css|media|ui|fonts)/~', $path);
            }
        }
    }

    public function testSharedAndLegacyHeadsUseCanonicalAssetsAndPreserveFavicons(): void
    {
        $root = dirname(__DIR__) . '/public';
        $pageTitle = $description = $keywords = $domain = $url = '';
        $poster = '/assets/media/poster.png';
        ob_start();
        try {
            require $root . '/head.php';
            $legacy = ob_get_contents();
        } finally {
            ob_end_clean();
        }
        self::assertIsString($legacy);
        foreach (['reading' => HomePage::render(), 'main' => $legacy] as $name => $html) {
            $previous = libxml_use_internal_errors(true);
            try {
                $document = new DOMDocument();
                self::assertTrue($document->loadHTML($html));
                $xpath = new DOMXPath($document);
                self::assertSame(
                    AssetUrl::versioned('/assets/css/' . $name . '.min.css'),
                    $xpath->evaluate('string(//link[@rel="stylesheet"]/@href)')
                );
                self::assertSame(
                    'render',
                    $xpath->evaluate('string(//link[@rel="stylesheet"]/@blocking)')
                );
                $favicons = '//link[starts-with(@href,"/favicons/")]';
                self::assertSame(6.0, $xpath->evaluate('count(' . $favicons . ')'));
                foreach ($xpath->query($favicons . '/@href') ?: [] as $href) {
                    self::assertFileExists($root . $href->nodeValue);
                }
            } finally {
                libxml_clear_errors();
                libxml_use_internal_errors($previous);
            }
        }
    }

    public function testLegacyAssetCompatibilityIncludesFontsButNotFavicons(): void
    {
        $config = file_get_contents(dirname(__DIR__) . '/public/.htaccess');
        self::assertIsString($config);
        self::assertStringContainsString(
            'RewriteRule ^(css|ui|media|fonts)/(.*)$ /assets/$1/$2 [R=301,L]',
            $config
        );
        self::assertStringNotContainsString('^favicons/', $config);
    }

    public function testAssetCheckRejectsMissingArtifactWithoutWritingIt(): void
    {
        $name = 'freshness-' . bin2hex(random_bytes(8));
        $directory = dirname(__DIR__) . '/public/assets/css/';
        $source = $directory . $name . '.css';
        $target = $directory . $name . '.min.css';
        try {
            self::assertNotFalse(file_put_contents($source, 'body { color: black; }'));
            $process = proc_open(
                [PHP_BINARY, dirname(__DIR__) . '/cli/minify-assets.php', '--check'],
                [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                $pipes
            );
            self::assertIsResource($process);
            stream_get_contents($pipes[1]);
            $errors = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            self::assertSame(1, proc_close($process));
            self::assertIsString($errors);
            self::assertStringContainsString($name . '.min.css', $errors);
            self::assertFileDoesNotExist($target);
        } finally {
            if (is_file($source)) {
                unlink($source);
            }
        }
    }
}
