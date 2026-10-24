<?php

declare(strict_types=1);

namespace MTM\Tests;

use MTM\Marketing\ResourcesIndex;
use MTM\Marketing\UserInterface\ContentFragment;
use MTM\Marketing\UserInterface\PageRenderer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ResourcesIndexTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/mtm-index-' . bin2hex(random_bytes(8));
        $directories = [
            'public_content/resources', 'public_content/coaching', 'public_content/item',
            'public/item'
        ];
        foreach ($directories as $dir) {
            mkdir($this->root . '/' . $dir, 0700, true);
        }
        self::write('public_content/resources/published.json', '["/item/"]');
        self::write('public_content/coaching/content.md', '## Coaching');
        self::write('public/item/index.php', '<?php');
        self::write('public_content/item/content.html', '<h1>Existing content</h1>');
        self::write('public_content/item/meta.json', self::metadata());
        mkdir($this->root . '/public_content/resources/the-action-loop', 0700, true);
        self::write(
            'public_content/resources/the-action-loop/content.md',
            "## Preparing\n\nExample."
        );
        mkdir($this->root . '/public_content/resources/motivators', 0700, true);
        self::write(
            'public_content/resources/motivators/content.md',
            "## Exercise\n\nTerm\n: Definition"
        );
        mkdir($this->root . '/public_content/resources/the-5-ps', 0700, true);
        self::write(
            'public_content/resources/the-5-ps/content.md',
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
        $html = ResourcesIndex::compile(dirname(__DIR__));
        self::assertSame($html, ResourcesIndex::compile(dirname(__DIR__)));
        self::assertSame($html, ContentFragment::forRoute('/resources/'));
        self::assertStringContainsString(
            ' - Model / Time',
            $html
        );
    }

    /** @return iterable<string, array{string}> */
    public static function resourceTypes(): iterable
    {
        foreach (['model', 'exercise', 'guide', 'reference', 'essay'] as $type) {
            yield $type => [$type];
        }
    }

    #[DataProvider('resourceTypes')]
    public function testPurposeTypesRenderWithoutAFormatField(string $type): void
    {
        self::write(
            'public_content/item/meta.json',
            str_replace('"type":"reference"', '"type":"' . $type . '"', self::metadata())
        );
        self::assertStringContainsString(
            ' - ' . ucfirst($type) . ' / Money',
            ResourcesIndex::compile($this->root)
        );
    }

    public function testExplicitSelectionIgnoresDraftsAncillaryAndUnlistedMetadata(): void
    {
        mkdir($this->root . '/content-root', 0700);
        self::write('content-root/meta.json', 'not json');
        self::write('public_content/resources/meta.json', 'not json');
        mkdir($this->root . '/public_content/unlisted', 0700);
        self::write('public_content/unlisted/meta.json', 'not json');
        $html = ResourcesIndex::compile($this->root);
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

    public function testUpdatedDateTitleAndRouteDetermineOrderNotManifestPosition(): void
    {
        $entries = [
            '/older/' => ['20251231', 'A first title'],
            '/z-title/' => ['20260511', 'Z last title'],
            '/b-tie/' => ['20260511', 'alpha'],
            '/a-tie/' => ['20260511', 'Alpha'],
            '/newest/' => ['20270101', 'Z newest title']
        ];
        foreach ($entries as $route => [$date, $title]) {
            mkdir($this->root . '/public' . $route, 0700, true);
            self::write('public' . $route . 'index.php', '<?php');
            $dir = 'public_content' . $route;
            mkdir($this->root . '/' . $dir, 0700, true);
            self::write($dir . 'content.html', '<p>Published</p>');
            $meta = json_decode(self::metadata(), true, 512, JSON_THROW_ON_ERROR);
            self::assertIsArray($meta);
            self::write(
                $dir . 'meta.json',
                json_encode(
                    array_replace($meta, ['updated' => $date, 'listingTitle' => $title]),
                    JSON_THROW_ON_ERROR
                )
            );
        }
        $expected = ['/newest/', '/a-tie/', '/b-tie/', '/z-title/', '/older/'];
        $previous = null;
        foreach ([array_keys($entries), array_reverse(array_keys($entries))] as $routes) {
            self::write(
                'public_content/resources/published.json',
                json_encode($routes, JSON_THROW_ON_ERROR)
            );
            $html = ResourcesIndex::compile($this->root);
            preg_match_all('/<a href="([^"]+)"/', $html, $matches);
            self::assertSame($expected, $matches[1]);
            if ($previous !== null) {
                self::assertSame($previous, $html);
            }
            $previous = $html;
        }
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
        yield 'former format type' => ['{"type":"article"}', 'type must'];
        yield 'download format' => ['{"type":"worksheet"}', 'type must'];
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
            $original = json_decode(self::metadata(), false, 512, JSON_THROW_ON_ERROR);
            $replacement = json_decode($override, false, 512, JSON_THROW_ON_ERROR);
            self::assertInstanceOf(\stdClass::class, $original);
            self::assertInstanceOf(\stdClass::class, $replacement);
            $json = json_encode(
                array_replace(get_object_vars($original), get_object_vars($replacement)),
                JSON_THROW_ON_ERROR
            );
        }
        self::write('public_content/item/meta.json', $json);
        parent::expectException(RuntimeException::class);
        parent::expectExceptionMessage($this->root . '/public_content/item/meta.json:');
        try {
            ResourcesIndex::compile($this->root);
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
        self::write('public_content/resources/published.json', $json);
        parent::expectException(RuntimeException::class);
        parent::expectExceptionMessage($this->root . '/public_content/resources/published.json:');
        ResourcesIndex::compile($this->root);
    }

    public function testMissingPublishedFilesFailExplicitly(): void
    {
        unlink($this->root . '/public_content/item/meta.json');
        parent::expectException(RuntimeException::class);
        parent::expectExceptionMessage('/item/meta.json: missing JSON file');
        ResourcesIndex::compile($this->root);
    }

    public function testMissingPublishedRouteFailsExplicitly(): void
    {
        unlink($this->root . '/public/item/index.php');
        parent::expectException(RuntimeException::class);
        parent::expectExceptionMessage('/item/meta.json: missing published entry point');
        ResourcesIndex::compile($this->root);
    }

    public function testPublicFragmentCannotReplaceMissingPrivateArtifact(): void
    {
        unlink($this->root . '/public_content/item/content.html');
        self::write('public/item/content.html', '<p>Stale public copy</p>');
        parent::expectException(RuntimeException::class);
        parent::expectExceptionMessage(
            '/public_content/item/meta.json: missing published entry point or content.html.'
        );
        ResourcesIndex::compile($this->root);
    }

    public function testCliChecksMissingStaleAndMetadataChangedArtifactsWithoutWriting(): void
    {
        mkdir($this->root . '/cli', 0700);
        mkdir($this->root . '/vendor', 0700);
        foreach (PageRenderer::ROUTES as $route) {
            $directory = ContentFragment::directory($route, $this->root);
            if (!is_dir($directory)) {
                mkdir($directory, 0700, true);
            }
            self::write(
                substr($directory, strlen($this->root) + 1) . 'meta.json',
                '{"title":"T","description":"D"}'
            );
        }
        copy(
            dirname(__DIR__) . '/cli/compile-content.php',
            $this->root . '/cli/compile-content.php'
        );
        self::write(
            'vendor/autoload.php',
            '<?php require ' . var_export(dirname(__DIR__) . '/vendor/autoload.php', true) . ';'
        );
        [$code, , $errors] = self::command('--check');
        self::assertSame(1, $code);
        self::assertStringContainsString('Stale or missing fragment:', $errors);
        self::assertFileDoesNotExist($this->root . '/public_content/resources/content.html');
        self::assertSame(0, self::command()[0]);
        $generated = file_get_contents($this->root . '/public_content/resources/content.html');
        self::assertSame(0, self::command()[0]);
        self::assertSame(
            $generated,
            file_get_contents($this->root . '/public_content/resources/content.html')
        );
        self::assertSame([0, "Verified 14 content artifact(s).\n", ''], self::command('--check'));
        $schemaPath = $this->root . '/public_content/services/structured-data.json';
        $schema = file_get_contents($schemaPath);
        self::assertIsString($schema);
        unlink($schemaPath);
        self::assertSame(1, self::command('--check')[0]);
        self::assertFileDoesNotExist($schemaPath);
        file_put_contents($schemaPath, '{');
        self::assertSame(1, self::command('--check')[0]);
        self::assertSame('{', file_get_contents($schemaPath));
        self::assertSame(0, self::command()[0]);
        self::assertSame($schema, file_get_contents($schemaPath));
        self::write('public_content/services/meta.json', '{"title":"Changed","description":"D"}');
        self::assertSame(1, self::command('--check')[0]);
        self::assertSame($schema, file_get_contents($schemaPath));
        self::write('public_content/services/meta.json', '{"title":"T","description":"D"}');
        self::write(
            'public_content/item/meta.json',
            str_replace('20240229', '20240301', self::metadata())
        );
        self::assertSame(1, self::command('--check')[0]);
        self::assertSame(
            $generated,
            file_get_contents($this->root . '/public_content/resources/content.html')
        );
        self::write(
            'public_content/item/meta.json',
            str_replace('Money', 'Space', self::metadata())
        );
        self::assertSame(1, self::command('--check')[0]);
        self::assertSame(
            $generated,
            file_get_contents($this->root . '/public_content/resources/content.html')
        );
        self::write('public_content/resources/content.html', 'stale');
        self::assertSame(1, self::command('--check')[0]);
        $target = $this->root . '/public_content/resources/content.html';
        self::assertSame('stale', file_get_contents($target));
        self::write('public_content/item/meta.json', '{');
        [$code, $output, $errors] = self::command();
        self::assertNotSame(0, $code);
        self::assertStringContainsString('/item/meta.json:', $output . $errors);
        self::assertSame('stale', file_get_contents($target));
        self::write('public_content/item/meta.json', self::metadata());
        self::write('public_content/resources/content.md', '[Unlisted](/unlisted/)');
        [$code, $output, $errors] = self::command();
        self::assertNotSame(0, $code);
        self::assertStringContainsString(
            '/resources/content.md: conflicts with the metadata-generated index.',
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
