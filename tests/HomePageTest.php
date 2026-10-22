<?php

declare(strict_types=1);

namespace MTM\Tests;

use DOMDocument;
use DOMXPath;
use MTM\Marketing\UserInterface\HomePage;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class HomePageTest extends TestCase
{
    /** @return iterable<string, array{?string}> */
    public static function invalidMetadata(): iterable
    {
        yield 'missing' => [null];
        yield 'malformed' => ['{'];
        yield 'not object' => ['[]'];
        yield 'missing field' => ['{"title":"Title","description":"Description"}'];
        yield 'wrong type' => ['{"heading":[],"title":"Title","description":"Description"}'];
        yield 'empty' => ['{"heading":"Heading","title":" ","description":"Description"}'];
    }

    #[DataProvider('invalidMetadata')]
    public function testInvalidHomeMetadataReportsItsPath(?string $json): void
    {
        $root = sys_get_temp_dir() . '/mtm-home-' . bin2hex(random_bytes(8));
        mkdir($root . '/src/Marketing/UserInterface', 0700, true);
        mkdir($root . '/public_content', 0700);
        $source = $root . '/src/Marketing/UserInterface/HomePage.php';
        copy(dirname(__DIR__) . '/src/Marketing/UserInterface/HomePage.php', $source);
        $meta = $root . '/public_content/meta.json';
        try {
            if ($json !== null) {
                file_put_contents($meta, $json);
            }
            $process = proc_open(
                [PHP_BINARY, '-r', 'require $argv[1];'
                    . ' MTM\\Marketing\\UserInterface\\HomePage::metadata();', $source],
                [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                $pipes
            );
            self::assertIsResource($process);
            $output = stream_get_contents($pipes[1]);
            $errors = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            self::assertNotSame(0, proc_close($process));
            self::assertStringContainsString($meta . ':', $output . $errors);
        } finally {
            if (is_file($meta)) {
                unlink($meta);
            }
            unlink($source);
            rmdir($root . '/src/Marketing/UserInterface');
            rmdir($root . '/src/Marketing');
            rmdir($root . '/src');
            rmdir($root . '/public_content');
            rmdir($root);
        }
    }

    public function testFourDomainsHaveHeadingsAndSupportDestinations(): void
    {
        $previous = libxml_use_internal_errors(true);
        try {
            $document = new DOMDocument();
            self::assertTrue($document->loadHTML(HomePage::render()));
            $xpath = new DOMXPath($document);
            self::assertSame(1.0, $xpath->evaluate('count(//h1)'));
            self::assertSame(4.0, $xpath->evaluate('count(//main/section)'));
            foreach (['time', 'people', 'space', 'money'] as $id) {
                $section = '//main/section[@aria-labelledby="' . $id . '"]';
                self::assertSame(
                    ucfirst($id),
                    $xpath->evaluate('string(' . $section . '/h2[@id="' . $id . '"])')
                );
                foreach (['coaching-and-mentoring', 'services'] as $destination) {
                    self::assertSame(
                        1.0,
                        $xpath->evaluate(
                            'count(' . $section . '//a[@href="/' . $destination . '/"])'
                        )
                    );
                }
            }
            self::assertSame(0.0, $xpath->evaluate('count(//main//h3|//form|//iframe)'));
            self::assertSame(1.0, $xpath->evaluate('count(//nav//a[@aria-current])'));
            self::assertSame(
                '/',
                $xpath->evaluate('string(//nav//a[@aria-current="page"]/@href)')
            );
            RenderedAnchorAssertions::assertHttpHostPolicy(
                $xpath,
                'https://mastering-the-mundane.com/'
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    public function testPhysicalHomeEntryUsesSharedPage(): void
    {
        ob_start();
        require dirname(__DIR__) . '/site-root/public/index.php';
        $html = ob_get_clean();
        self::assertSame(HomePage::render(), $html);
    }
}
