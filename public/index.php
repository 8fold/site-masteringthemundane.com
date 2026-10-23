<?php

declare(strict_types=1);

use MTM\Marketing\UserInterface\HomePage;

require_once dirname(__DIR__) . '/vendor/autoload.php';

echo HomePage::render();
