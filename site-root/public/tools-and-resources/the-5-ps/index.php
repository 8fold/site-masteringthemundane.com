<?php

declare(strict_types=1);

use MTM\Marketing\UserInterface\FivePsPage;

require_once dirname(__DIR__, 4) . '/vendor/autoload.php';

echo FivePsPage::render();
