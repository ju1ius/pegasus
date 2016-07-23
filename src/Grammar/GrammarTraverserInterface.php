<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Grammar;

use ju1ius\Pegasus\Grammar;

/**
 * Interface for Grammar traversers.
 */
interface GrammarTraverserInterface
{
    /**
     * Adds a visitor.
     *
     * @param GrammarVisitorInterface[] ...$visitors
     *
     * @return $this
     */
    public function addVisitor(GrammarVisitorInterface ...$visitors);

    /**
     * Removes an added visitor.
     *
     * @param GrammarVisitorInterface[] ...$visitors
     *
     * @return $this
     *
     */
    public function removeVisitor(GrammarVisitorInterface ...$visitors);

    /**
     * Traverses a grammar and it's expression tree using the registered optimizations.
     *
     * @param Grammar $grammar The grammar to traverse
     *
     * @return mixed The result of the traversal.
     */
    public function traverse(Grammar $grammar);
}
