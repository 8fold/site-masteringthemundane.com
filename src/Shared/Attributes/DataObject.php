<?php

declare(strict_types=1);

namespace MTM\Shared\Attributes;

use Attribute;

/**
 * Marks a class as a data object whose primary purpose is holding property
 * values rather than coordinating application behavior.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class DataObject
{
}
