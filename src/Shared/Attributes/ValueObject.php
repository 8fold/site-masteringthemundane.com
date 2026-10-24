<?php

declare(strict_types=1);

namespace MTM\Shared\Attributes;

use Attribute;

/**
 * Marks a value object: an immutable data object used as the type of one
 * member of another data object. It is not restricted to database values.
 *
 * The DataObject contract applies: property access only after construction,
 * no side effects, and get-only computed properties. Parsing, generation,
 * validation factories, and other public methods belong in collaborators,
 * not on the value object.
 *
 * #[ValueObject] already identifies a data object; do not also add
 * #[DataObject]. The attribute is a marker, not runtime enforcement.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class ValueObject
{
}
