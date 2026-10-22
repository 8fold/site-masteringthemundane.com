<?php

declare(strict_types=1);

use MTM\Marketing\UserInterface\HomePage;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

echo HomePage::render();
