<?php

declare(strict_types=1);

use MatthiasMullie\Minify\CSS;

require_once dirname(__DIR__) . '/vendor/autoload.php';

if (!isset($argc, $argv)) {
    throw new RuntimeException('Run the asset minifier from the command line.');
}
if ($argc > 2 || ($argc === 2 && $argv[1] !== '--check')) {
    fwrite(STDERR, "Usage: php cli/minify-assets.php [--check]\n");
    exit(1);
}
$check = $argc === 2;
$sources = glob(dirname(__DIR__) . '/public/assets/css/*.css');
if ($sources === false) {
    throw new RuntimeException('Cannot enumerate stylesheets.');
}
$stale = [];
foreach ($sources as $source) {
    if (str_ends_with($source, '.min.css')) {
        continue;
    }
    $target = substr($source, 0, -4) . '.min.css';
    $content = (new CSS($source))->minify();
    if ($check) {
        if (!is_file($target) || file_get_contents($target) !== $content) {
            $stale[] = basename($target);
        }
    } elseif (file_put_contents($target, $content) === false) {
        throw new RuntimeException('Cannot write minified asset: ' . $target);
    }
}
if ($stale !== []) {
    fwrite(STDERR, 'Stale assets: ' . implode(', ', $stale) . '. Run composer minify.' . PHP_EOL);
    exit(1);
}
echo $check ? 'Minified assets are current.' . PHP_EOL : 'Minified stylesheets.' . PHP_EOL;
