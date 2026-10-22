<?php

declare(strict_types=1);

use MTM\Marketing\UserInterface\ResourcesPage;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

echo ResourcesPage::render();
