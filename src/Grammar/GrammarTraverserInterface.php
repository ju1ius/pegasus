<?php declare(strict_types=1);

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
     * @param GrammarVisitorInterface ...$visitors
     *
     * @return $this
     */
    public function addVisitor(GrammarVisitorInterface ...$visitors): static;

    /**
     * Removes an added visitor.
     *
     * @param GrammarVisitorInterface ...$visitors
     *
     * @return $this
     *
     */
    public function removeVisitor(GrammarVisitorInterface ...$visitors): static;

    /**
     * Traverses a grammar and it's expression tree using the registered optimizations.
     *
     * @param Grammar $grammar The grammar to traverse
     *
     * @return mixed The result of the traversal.
     */
    public function traverse(Grammar $grammar);
}
