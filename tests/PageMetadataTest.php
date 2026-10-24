<?php

declare(strict_types=1);

namespace MTM\Tests;

use DOMDocument;
use DOMXPath;
use MTM\Marketing\UserInterface\ActionLoopPage;
use MTM\Marketing\UserInterface\BooksPage;
use MTM\Marketing\UserInterface\CoachingPage;
use MTM\Marketing\UserInterface\ContentFragment;
use MTM\Marketing\UserInterface\FivePsPage;
use MTM\Marketing\UserInterface\HomePage;
use MTM\Marketing\UserInterface\MotivatorsPage;
use MTM\Marketing\UserInterface\PageMetadata;
use MTM\Marketing\UserInterface\ResourcesPage;
use MTM\Marketing\UserInterface\TimeBookPage;
use MTM\Shared\ReadingPage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class PageMetadataTest extends TestCase
{
    /** @return iterable<string, array{string, callable(): string}> */
    public static function pages(): iterable
    {
        yield 'home' => ['/', HomePage::render(...)];
        yield 'coaching' => ['/coaching-and-mentoring/', CoachingPage::render(...)];
        yield 'services' => [
            '/services/',
            static fn (): string => PageMetadata::render('/services/')
        ];
        yield 'resources' => ['/resources/', ResourcesPage::render(...)];
        yield 'action loop' => ['/resources/the-action-loop/', ActionLoopPage::render(...)];
        yield 'motivators' => ['/resources/motivators/', MotivatorsPage::render(...)];
        yield 'the 5 ps' => ['/resources/the-5-ps/', FivePsPage::render(...)];
        yield 'books' => ['/books/', BooksPage::render(...)];
        yield 'time book' => ['/books/time-mastering-the-mundane/', TimeBookPage::render(...)];
    }

    /** @param callable(): string $render */
    #[DataProvider('pages')]
    public function testPageRendersTitleStackAndDescriptionFromMetadata(
        string $route,
        callable $render
    ): void {
        self::assertContains($route, PageMetadata::ROUTES);
        $meta = PageMetadata::forRoute($route);
        self::assertSame('Mastering the Mundane', $meta['titles'][count($meta['titles']) - 1]);
        $title = implode(' | ', $meta['titles']);
        $xpath = self::parse($render());
        self::assertSame($meta['titles'][0], $xpath->evaluate('string(//header/h1)'));
        self::assertSame($title, $xpath->evaluate('string(//title)'));
        foreach (['og:title', 'twitter:title'] as $property) {
            self::assertSame($title, self::metaContent($xpath, $property));
        }
        foreach (['description', 'og:description', 'twitter:description'] as $property) {
            self::assertSame($meta['description'], self::metaContent($xpath, $property));
        }
    }

    public function testTitleStackFollowsRouteAncestry(): void
    {
        self::assertSame(
            ['Mastering the Mundane'],
            PageMetadata::forRoute('/')['titles']
        );
        self::assertSame(
            ['Services', 'Mastering the Mundane'],
            PageMetadata::forRoute('/services/')['titles']
        );
        self::assertSame(
            [
                'Action loop',
                'Resources',
                'Mastering the Mundane'
            ],
            PageMetadata::forRoute('/resources/the-action-loop/')['titles']
        );
    }

    public function testRoutesCoverEveryReadingPageOnce(): void
    {
        self::assertSame(
            array_column(iterator_to_array(self::pages()), 0),
            PageMetadata::ROUTES
        );
    }

    public function testResourceDetailsKeepDiscoveryMetadataBesidePageMetadata(): void
    {
        $listingTitles = [
            '/resources/the-action-loop/' =>
                'The Action Loop: Work Sustainably and Get Things Done',
            '/resources/motivators/' => 'The Motivators Exercise: Clarify Your Core Drivers',
            '/resources/the-5-ps/' =>
                'The 5 Ps Framework: Design Your Life (Passions, Pensives, More)'
        ];
        foreach (array_keys($listingTitles) as $route) {
            $path = ContentFragment::directory($route, dirname(__DIR__)) . 'meta.json';
            $json = file_get_contents($path);
            self::assertIsString($json);
            $meta = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            self::assertIsArray($meta);
            self::assertSame($listingTitles[$route], $meta['listingTitle']);
            self::assertSame(
                [
                    'title', 'description', 'created', 'updated', 'priority',
                    'order', 'listingTitle', 'type', 'domain', 'tags'
                ],
                array_keys($meta)
            );
        }
    }

    public function testMetadataTextIsEscapedInRenderedOutput(): void
    {
        $text = 'A & <b>B</b> "quoted" \'single\'';
        $html = ReadingPage::render('/resources/', [$text, $text], $text, '');
        self::assertStringNotContainsString('<b>B</b>', $html);
        $xpath = self::parse($html);
        self::assertSame($text, $xpath->evaluate('string(//header/h1)'));
        self::assertSame(0.0, $xpath->evaluate('count(//header/h1/*)'));
        self::assertSame($text . ' | ' . $text, $xpath->evaluate('string(//title)'));
        self::assertSame($text . ' | ' . $text, self::metaContent($xpath, 'og:title'));
        foreach (['description', 'twitter:description'] as $property) {
            self::assertSame($text, self::metaContent($xpath, $property));
        }
    }

    /** @return iterable<string, array{?string, string}> */
    public static function invalidMetadata(): iterable
    {
        yield 'missing' => [null, 'missing metadata'];
        yield 'malformed' => ['{', 'Syntax error'];
        yield 'not object' => ['[]', 'expected a metadata object'];
        yield 'missing field' => [
            '{"description":"Description"}',
            'title must be a non-empty string'
        ];
        yield 'wrong type' => [
            '{"title":[],"description":"Description"}',
            'title must be a non-empty string'
        ];
        yield 'empty' => [
            '{"title":"Title","description":" "}',
            'description must be a non-empty string'
        ];
        yield 'separate heading' => [
            '{"heading":"Heading","title":"Title","description":"Description"}',
            'heading is not supported'
        ];
        yield 'legacy-only resource fields' => [
            '{"title":"Title","listingTitle":"Listing","type":"model"}',
            'description must be a non-empty string'
        ];
    }

    #[DataProvider('invalidMetadata')]
    public function testInvalidMetadataReportsItsPathForEveryRoute(
        ?string $json,
        string $message
    ): void {
        $root = self::temporaryRoot();
        try {
            foreach (PageMetadata::ROUTES as $route) {
                $directory = ContentFragment::directory($route, $root);
                mkdir($directory, 0700, true);
                if ($json !== null) {
                    file_put_contents($directory . 'meta.json', $json);
                }
                try {
                    PageMetadata::forRoute($route, $root);
                    self::fail('Expected invalid metadata to fail for ' . $route);
                } catch (RuntimeException $exception) {
                    self::assertStringStartsWith(
                        $directory . 'meta.json: ',
                        $exception->getMessage()
                    );
                    self::assertStringContainsString($message, $exception->getMessage());
                }
            }
        } finally {
            self::remove($root);
        }
    }

    public function testBuildRejectsInvalidPageMetadataBeforeWritingFragments(): void
    {
        $root = self::temporaryRoot();
        try {
            mkdir($root . '/cli', 0700, true);
            mkdir($root . '/vendor', 0700);
            copy(dirname(__DIR__) . '/cli/compile-content.php', $root . '/cli/compile-content.php');
            file_put_contents(
                $root . '/vendor/autoload.php',
                '<?php require ' . var_export(dirname(__DIR__) . '/vendor/autoload.php', true) . ';'
            );
            foreach (PageMetadata::ROUTES as $route) {
                $directory = ContentFragment::directory($route, $root);
                mkdir($directory, 0700, true);
                file_put_contents(
                    $directory . 'meta.json',
                    '{"title":"T","description":"D"}'
                );
            }
            $invalid = ContentFragment::directory('/services/', $root) . 'meta.json';
            file_put_contents($invalid, '{"title":"T"}');
            file_put_contents($root . '/public_content/services/content.md', 'Services');
            $process = proc_open(
                [PHP_BINARY, $root . '/cli/compile-content.php'],
                [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                $pipes,
                $root
            );
            self::assertIsResource($process);
            $output = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            self::assertNotSame(0, proc_close($process));
            $realInvalid = realpath($root) . substr($invalid, strlen($root));
            self::assertStringContainsString(
                $realInvalid . ': description must be a non-empty string.',
                $output
            );
            self::assertFileDoesNotExist($root . '/public_content/services/content.html');
        } finally {
            self::remove($root);
        }
    }

    private function parse(string $html): DOMXPath
    {
        $previous = libxml_use_internal_errors(true);
        try {
            $document = new DOMDocument();
            self::assertTrue($document->loadHTML('<?xml encoding="utf-8"?>' . $html));
            return new DOMXPath($document);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    private function metaContent(DOMXPath $xpath, string $property): string
    {
        $value = $xpath->evaluate(
            'string(//meta[@name="' . $property . '" or @property="' . $property . '"]/@content)'
        );
        self::assertIsString($value);
        return $value;
    }

    private function temporaryRoot(): string
    {
        $root = sys_get_temp_dir() . '/mtm-meta-' . bin2hex(random_bytes(8));
        mkdir($root, 0700);
        return $root;
    }

    private function remove(string $root): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $file) {
            self::assertInstanceOf(\SplFileInfo::class, $file);
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($root);
    }
}
