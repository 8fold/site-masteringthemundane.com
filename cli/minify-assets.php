<?php

declare(strict_types=1);

/**
 * Minifies every source stylesheet and script in public/assets, writing a
 * sibling .min file. Existing .min files are artifacts and are skipped.
 *
 * Generated files are committed, verified fresh by CI and deployed as-is;
 * no compiling or transpiling happens on the server. The --check mode
 * verifies freshness without writing. Only the Composer autoloader is needed.
 */

use MatthiasMullie\Minify;

require_once dirname(__DIR__) . '/vendor/autoload.php';

if (!isset($argc, $argv)) {
    throw new RuntimeException('Run the asset minifier from the command line.');
}
if ($argc > 2 || ($argc === 2 && $argv[1] !== '--check')) {
    fwrite(STDERR, "Usage: php cli/minify-assets.php [--check]\n");
    exit(1);
}
$check = $argc === 2;
$assetTargets = [
    [__DIR__ . '/../public/assets/css', 'css', Minify\CSS::class],
    [__DIR__ . '/../public/assets/js', 'js', Minify\JS::class],
];
$stale = [];
foreach ($assetTargets as [$directory, $extension, $minifierClass]) {
    if (!is_dir($directory)) {
        continue;
    }
    $sources = glob($directory . '/*.' . $extension);
    if ($sources === false) {
        throw new RuntimeException('Cannot enumerate assets: ' . $directory);
    }
    foreach ($sources as $source) {
        if (str_ends_with(basename($source), '.min.' . $extension)) {
            continue;
        }
        $target = substr($source, 0, -(strlen($extension) + 1)) . '.min.' . $extension;
        $content = (new $minifierClass($source))->minify();
        if ($check) {
            if (!is_file($target) || file_get_contents($target) !== $content) {
                $stale[] = basename($target);
            }
        } elseif (file_put_contents($target, $content) === false) {
            throw new RuntimeException('Cannot write minified asset: ' . $target);
        } else {
            echo 'minified: ' . basename($source) . ' -> ' . basename($target) . PHP_EOL;
        }
    }
}
if ($stale !== []) {
    fwrite(STDERR, 'Stale assets: ' . implode(', ', $stale) . '. Run composer minify.' . PHP_EOL);
    exit(1);
}
echo $check ? 'Minified assets are current.' . PHP_EOL : 'Minified assets.' . PHP_EOL;
