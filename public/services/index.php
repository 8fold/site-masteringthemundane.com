<?php

declare(strict_types=1);

use MTM\Marketing\UserInterface\ContentFragment;
use MTM\Marketing\UserInterface\PageRenderer;
use MTM\Shared\UserInterface\DocumentBase;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

$route = '/services/';
$meta = PageRenderer::forRoute($route);

$document = DocumentBase::create(
    $route,
    $meta['titles'],
    $meta['description']
)->content(ContentFragment::forRoute($route));

echo $document;
