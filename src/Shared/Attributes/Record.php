<?php

declare(strict_types=1);

namespace MTM\Shared\Attributes;

use Attribute;

/**
 * Marks a record object: an immutable data object representing a whole
 * database row, with a class name suffixed Record.
 *
 * The DataObject contract applies: property access only after construction,
 * no side effects, and get-only computed properties. The public constructor
 * mirrors the table's constraints: required NOT NULL values first, optional
 * nullable values last. Database access and static factories belong elsewhere.
 *
 * #[Record] already identifies a data object; do not also add #[DataObject].
 * The attribute is a marker, not runtime enforcement or database integration.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class Record
{
}
