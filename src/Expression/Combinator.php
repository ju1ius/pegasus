<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Expression;

/**
 * A composite expression which contains one or more sub-expression.
 * ATM it does nothing more than Composite, and is here only for easier type-checking in optimizations.
 *
 * Subclassed by `Sequence`, `OneOf`
 */
abstract class Combinator extends Composite
{

}
