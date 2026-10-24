<?php

declare(strict_types=1);

namespace MTM;

use MTM\Shared\Attributes\DataObject;

#[DataObject]
final readonly class PageMetadata
{
    public function __construct(
        public string $title,
        public string $description
    ) {
    }
}
