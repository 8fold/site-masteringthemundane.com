<?php

declare(strict_types=1);

namespace MTM\Tests;

use MTM\Marketing\ArticlesIndex;
use MTM\Marketing\UserInterface\ContentFragment;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ArticlesIndexTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/mtm-index-' . bin2hex(random_bytes(8));
        $directories = [
            'public_content/articles', 'public_content/coaching', 'site-root/public/item'
        ];
        foreach ($directories as $dir) {
            mkdir($this->root . '/' . $dir, 0700, true);
        }
        $this->write('public_content/articles/published.json', '["/item/"]');
        $this->write('public_content/coaching/content.md', '## Coaching');
        $this->write('site-root/public/item/index.php', '<?php');
        $this->write('site-root/public/item/content.html', '<h1>Existing content</h1>');
        $this->write('site-root/public/item/meta.json', $this->metadata());
        mkdir($this->root . '/site-root/public/articles/the-action-loop', 0700, true);
        $this->write(
            'site-root/public/articles/the-action-loop/content.md',
            "## Preparing\n\nExample."
        );
        mkdir($this->root . '/site-root/public/tools-and-resources/motivators', 0700, true);
        $this->write(
            'site-root/public/tools-and-resources/motivators/content.md',
            "## Exercise\n\nTerm\n: Definition"
        );
        mkdir($this->root . '/site-root/public/tools-and-resources/the-5-ps', 0700, true);
        $this->write(
            'site-root/public/tools-and-resources/the-5-ps/content.md',
            "## Another exercise\n\nTerm\n: Definition"
        );
    }

    protected function tearDown(): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->root, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $file) {
            self::assertInstanceOf(\SplFileInfo::class, $file);
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }
        rmdir($this->root);
    }

    public function testCommittedIndexIsFreshAndDeterministic(): void
    {
        $html = ArticlesIndex::compile(dirname(__DIR__));
        self::assertSame($html, ArticlesIndex::compile(dirname(__DIR__)));
        self::assertSame($html, ContentFragment::forRoute('/articles/'));
    }

    public function testExplicitSelectionIgnoresDraftsAncillaryAndUnlistedMetadata(): void
    {
        mkdir($this->root . '/content-root', 0700);
        $this->write('content-root/meta.json', 'not json');
        $this->write('public_content/articles/meta.json', 'not json');
        mkdir($this->root . '/site-root/public/unlisted', 0700);
        $this->write('site-root/public/unlisted/meta.json', 'not json');
        $html = ArticlesIndex::compile($this->root);
        self::assertSame(
            "<ul>\n<li><a href=\"/item/\">A &amp; &lt;B&gt; &quot;title&quot;</a>"
                . ' - Reference / Money<br>Last updated: <time datetime="2024-02-29">'
                . "February 29, 2024</time></li>\n</ul>\n",
            $html
        );
        self::assertStringNotContainsString('Wrong legacy title', $html);
        self::assertStringNotContainsString('20210623', $html);
        self::assertStringNotContainsString('unlisted', $html);
    }

    /** @return iterable<string, array{string, string}> */
    public static function invalidMetadata(): iterable
    {
        yield 'malformed JSON' => ['{', 'Syntax error'];
        yield 'not an object' => ['[]', 'metadata object'];
        yield 'missing fields' => ['{}', 'listingTitle'];
        yield 'invalid title' => ['{"listingTitle":[]}', 'listingTitle'];
        yield 'empty title' => ['{"listingTitle":" "}', 'listingTitle'];
        yield 'unknown type' => ['{"type":"video"}', 'type must'];
        yield 'invalid type' => ['{"type":[]}', 'type must'];
        yield 'unknown domain' => ['{"domain":"Tools"}', 'domain must'];
        yield 'wrong domain case' => ['{"domain":"time"}', 'domain must'];
        yield 'invalid domain' => ['{"domain":1}', 'domain must'];
        yield 'tags not a list' => ['{"tags":{}}', 'tags must'];
        yield 'tags not strings' => ['{"tags":[1]}', 'tags must'];
        yield 'empty tag' => ['{"tags":[" "]}', 'tags must'];
        yield 'null tags' => ['{"tags":null}', 'tags must'];
        yield 'null date' => ['{"updated":null}', 'updated must'];
        yield 'numeric date' => ['{"updated":20240229}', 'updated must'];
        yield 'wrong date format' => ['{"updated":"2024-02-29"}', 'updated must'];
        yield 'impossible date' => ['{"updated":"20240230"}', 'updated must'];
        yield 'non-leap year' => ['{"updated":"20230229"}', 'updated must'];
        yield 'invalid month' => ['{"updated":"20241301"}', 'updated must'];
    }

    #[DataProvider('invalidMetadata')]
    public function testInvalidMetadataReportsItsPath(string $override, string $message): void
    {
        if (in_array($override, ['{', '[]', '{}'], true)) {
            $json = $override;
        } else {
            $original = json_decode($this->metadata(), false, 512, JSON_THROW_ON_ERROR);
            $replacement = json_decode($override, false, 512, JSON_THROW_ON_ERROR);
            self::assertInstanceOf(\stdClass::class, $original);
            self::assertInstanceOf(\stdClass::class, $replacement);
            $json = json_encode(
                array_replace(get_object_vars($original), get_object_vars($replacement)),
                JSON_THROW_ON_ERROR
            );
        }
        $this->write('site-root/public/item/meta.json', $json);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($this->root . '/site-root/public/item/meta.json:');
        try {
            ArticlesIndex::compile($this->root);
        } catch (RuntimeException $exception) {
            self::assertStringContainsString($message, $exception->getMessage());
            throw $exception;
        }
    }

    /** @return iterable<string, array{string}> */
    public static function invalidCollections(): iterable
    {
        foreach (['{', '{}', '[]', '["/item/","/item/"]', '["/../item/"]', '[1]'] as $json) {
            yield $json => [$json];
        }
    }

    #[DataProvider('invalidCollections')]
    public function testInvalidCollectionsReportManifestPath(string $json): void
    {
        $this->write('public_content/articles/published.json', $json);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($this->root . '/public_content/articles/published.json:');
        ArticlesIndex::compile($this->root);
    }

    public function testMissingPublishedFilesFailExplicitly(): void
    {
        unlink($this->root . '/site-root/public/item/meta.json');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('/item/meta.json: missing JSON file');
        ArticlesIndex::compile($this->root);
    }

    public function testMissingPublishedRouteFailsExplicitly(): void
    {
        unlink($this->root . '/site-root/public/item/index.php');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('/item/meta.json: missing published entry point');
        ArticlesIndex::compile($this->root);
    }

    public function testCliChecksMissingStaleAndMetadataChangedArtifactsWithoutWriting(): void
    {
        mkdir($this->root . '/cli', 0700);
        mkdir($this->root . '/vendor', 0700);
        copy(
            dirname(__DIR__) . '/cli/compile-content.php',
            $this->root . '/cli/compile-content.php'
        );
        $this->write(
            'vendor/autoload.php',
            '<?php require ' . var_export(dirname(__DIR__) . '/vendor/autoload.php', true) . ';'
        );
        [$code, , $errors] = $this->command('--check');
        self::assertSame(1, $code);
        self::assertStringContainsString('Stale or missing fragment:', $errors);
        self::assertFileDoesNotExist($this->root . '/public_content/articles/content.html');
        self::assertSame(0, $this->command()[0]);
        $generated = file_get_contents($this->root . '/public_content/articles/content.html');
        self::assertSame(0, $this->command()[0]);
        self::assertSame(
            $generated,
            file_get_contents($this->root . '/public_content/articles/content.html')
        );
        self::assertSame([0, "Verified 5 content fragment(s).\n", ''], $this->command('--check'));
        $this->write(
            'site-root/public/item/meta.json',
            str_replace('20240229', '20240301', $this->metadata())
        );
        self::assertSame(1, $this->command('--check')[0]);
        self::assertSame(
            $generated,
            file_get_contents($this->root . '/public_content/articles/content.html')
        );
        $this->write(
            'site-root/public/item/meta.json',
            str_replace('Money', 'Space', $this->metadata())
        );
        self::assertSame(1, $this->command('--check')[0]);
        self::assertSame(
            $generated,
            file_get_contents($this->root . '/public_content/articles/content.html')
        );
        $this->write('public_content/articles/content.html', 'stale');
        self::assertSame(1, $this->command('--check')[0]);
        $target = $this->root . '/public_content/articles/content.html';
        self::assertSame('stale', file_get_contents($target));
        $this->write('site-root/public/item/meta.json', '{');
        [$code, $output, $errors] = $this->command();
        self::assertNotSame(0, $code);
        self::assertStringContainsString('/item/meta.json:', $output . $errors);
        self::assertSame('stale', file_get_contents($target));
        $this->write('site-root/public/item/meta.json', $this->metadata());
        $this->write('public_content/articles/content.md', '[Unlisted](/unlisted/)');
        [$code, $output, $errors] = $this->command();
        self::assertNotSame(0, $code);
        self::assertStringContainsString(
            '/articles/content.md: conflicts with the metadata-generated index.',
            $output . $errors
        );
        self::assertSame('stale', file_get_contents($target));
    }

    private function metadata(): string
    {
        return '{"title":"Wrong legacy title","created":"20210623","updated":"20240229",'
            . '"listingTitle":"A & <B> \"title\"","type":"reference","domain":"Money","tags":[]}';
    }

    private function write(string $path, string $contents): void
    {
        self::assertSame(
            strlen($contents),
            file_put_contents($this->root . '/' . $path, $contents)
        );
    }

    /**
     * @phpstan-impure
     * @return array{int, string, string}
     */
    private function command(string ...$arguments): array
    {
        $process = proc_open(
            [PHP_BINARY, $this->root . '/cli/compile-content.php', ...array_values($arguments)],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            $this->root
        );
        self::assertIsResource($process);
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        self::assertIsString($output);
        self::assertIsString($errors);
        return [proc_close($process), $output, $errors];
    }
}
