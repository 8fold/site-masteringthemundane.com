<?php

declare(strict_types=1);

namespace MTM\Shared\Attributes;

use Attribute;

/**
 * Marks an immutable data object: typed property values and side-effect-free
 * computed properties, with no public methods other than construction.
 * Consumers read properties; changed data requires a new instance.
 *
 * Use a final readonly class with a public constructor that initializes its
 * values without side effects. Get-only property hooks may derive values from
 * those properties without changing state. File/database access, parsing,
 * factories, and application operations belong in separate collaborators.
 * Referenced objects must also be immutable; readonly alone is shallow.
 *
 * Record and ValueObject are specializations of this contract. Apply
 * #[DataObject] only when neither specialization fits; do not repeat it on a
 * class already marked #[Record] or #[ValueObject].
 *
 * The marker makes the contract discoverable through reflection and static
 * analysis. It does not itself enforce immutability or absence of side effects.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class DataObject
{
}
