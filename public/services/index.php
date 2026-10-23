<?php

declare(strict_types=1);

use MTM\Marketing\UserInterface\ServicesPage;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

echo ServicesPage::render();
