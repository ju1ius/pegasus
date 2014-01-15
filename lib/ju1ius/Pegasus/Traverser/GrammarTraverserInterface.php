<?php

namespace ju1ius\Pegasus\Traverser;

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Visitor\GrammarVisitorInterface;


/**
 * Interface for Grammar traversers.
 *
 */
interface GrammarTraverserInterface
{
    /**
     * Adds a visitor.
     *
     * @param GrammarVisitorInterface $visitor Visitor to add
	 *
	 * @return $this
     */
    public function addVisitor(GrammarVisitorInterface $visitor);

    /**
     * Removes an added visitor.
     *
     * @param GrammarVisitorInterface $visitor
	 *
	 * @return $this
     */
    public function removeVisitor(GrammarVisitorInterface $visitor);

    /**
     * Traverses a grammar and it's expression tree using the registered visitors.
     *
     * @param ju1ius\Pegasus\Grammar $grammar The grammar to traverse
     *
     * @return mixed The result of the traversal.
     */
    public function traverse(Grammar $grammar);
}
