<?php

declare(strict_types=1);

use MTM\Marketing\UserInterface\ContentFragment;
use MTM\Marketing\UserInterface\PageMetadata;
use MTM\Shared\UserInterface\DocumentBase;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

$route = '/services/';
$meta = PageMetadata::forRoute($route);

echo DocumentBase::render(
    $route,
    $meta['titles'],
    $meta['description'],
    ContentFragment::forRoute($route)
);
