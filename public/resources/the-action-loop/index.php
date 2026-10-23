<?php

declare(strict_types=1);

use MTM\Marketing\UserInterface\ActionLoopPage;

require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

echo ActionLoopPage::render();
