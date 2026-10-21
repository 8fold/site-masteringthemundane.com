<?php

declare(strict_types=1);

use MTM\Marketing\ContentCompiler;
use MTM\Marketing\ArticlesIndex;

require_once __DIR__ . '/../vendor/autoload.php';

if (!isset($argc, $argv)) {
    throw new RuntimeException('Run the content compiler from the command line.');
}

if ($argc > 2 || ($argc === 2 && $argv[1] !== '--check')) {
    fwrite(STDERR, "Usage: php cli/compile-content.php [--check]\n");
    exit(1);
}

$check = $argc === 2;
$sources = [
    __DIR__ . '/../site-root/public/tools-and-resources/motivators/content.md'
];
$entries = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(__DIR__ . '/../public_content', FilesystemIterator::SKIP_DOTS)
);
foreach ($entries as $entry) {
    if ($entry instanceof SplFileInfo && $entry->getExtension() === 'md') {
        $sources[] = $entry->getPathname();
    }
}
sort($sources, SORT_STRING);

$artifacts = [
    __DIR__ . '/../public_content/articles/content.html' => [
        'html' => ArticlesIndex::compile(__DIR__ . '/..'),
        'source' => __DIR__ . '/../public_content/articles/published.json'
    ]
];
foreach ($sources as $source) {
    $markdown = file_get_contents($source);
    if ($markdown === false) {
        throw new RuntimeException('Could not read Markdown: ' . $source);
    }
    $target = substr($source, 0, -3) . '.html';
    if (isset($artifacts[$target])) {
        throw new RuntimeException($source . ': conflicts with the metadata-generated index.');
    }
    $artifacts[$target] = ['html' => ContentCompiler::compile($markdown), 'source' => $source];
}
ksort($artifacts, SORT_STRING);

$stale = false;
foreach ($artifacts as $target => $artifact) {
    $html = $artifact['html'];
    if ($check) {
        if (!is_file($target) || file_get_contents($target) !== $html) {
            fwrite(
                STDERR,
                'Stale or missing fragment: ' . $target . "; run composer compile-content.\n"
            );
            $stale = true;
        }
    } else {
        if (file_put_contents($target, $html) !== strlen($html)) {
            throw new RuntimeException('Could not write fragment: ' . $target);
        }
        $relativeSource = substr($artifact['source'], strlen(__DIR__ . '/..'));
        echo 'compiled (mastering-the-mundane.com): ' . $relativeSource
            . ' -> ' . basename($target) . PHP_EOL;
    }
}

if ($stale) {
    exit(1);
}
if ($check) {
    echo 'Verified ' . count($artifacts) . " content fragment(s).\n";
}
