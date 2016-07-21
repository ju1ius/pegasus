<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Expression;

/**
 * A composite expression which contains one or more sub-expression.
 * ATM it does nothing more than Composite, and is here only for easier type-checking in optimizations.
 *
 * Subclassed by `Sequence`, `NamedSequence`, `OneOf`
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
abstract class Combinator extends Composite
{

}
