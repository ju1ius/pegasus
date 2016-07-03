<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus;

interface GrammarInterface extends \ArrayAccess, \Countable, \IteratorAggregate
{
    /**
     * Get the grammar's name.
     *
     * @return string
     */
    public function getName();

    /**
     * Set the grammar's name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name);

    /**
     * Returns the rules for this grammar, as a mapping
     * between from rule names to Expression objects.
     *
     * @return Expression[]
     */
    public function getRules();

    /**
     * Sets the default start rule for this grammar.
     *
     * @param string $name The name of the rule to use as start rule.
     *
     * @throw \ju1ius\Pegasus\Grammar\Exception\RuleNotFound If the rule wasn't found in the grammar.
     *
     * @return GrammarInterface $this
     */
    public function setStartRule($name);

    /**
     * Returns the start rule of this grammar.
     *
     * @throw \ju1ius\Pegasus\Grammar\Exception\RuleNotFound If no start rule was found.
     *
     * @return Expression
     */
    public function getStartRule();

    /**
     * Returns a new (unfolded) grammar object containing the rules
     * of this instance merged with rules of $other.
     *
     * Rules with the same name will be overriden.
     *
     * @param GrammarInterface $other The grammar to merge into this one.
     *
     * @return GrammarInterface
     */
    public function merge(GrammarInterface $other);

    /**
     * Folds the grammar by resolving Reference objects
     * to actual references to the corresponding expressions.
     *
     * @deprecated since version 0.2 Grammars don't need to be folded before matching anymore.
     *
     * @param string $startRule An optional default start rule to use.
     *
     * @return GrammarInterface $this
     */
    public function fold($startRule = null);

    /**
     * Unfolds the grammar by converting circular references to Reference objects.
     *
     * @return GrammarInterface $this
     */
    public function unfold();

    /**
     * Returns whether the grammar is in folded state.
     *
     * @return bool True if the grammar is in folded state.
     */
    public function isFolded();

    /**
     * Prepares the grammar for matching.
     *
     * Folds the grammar and performs additional optimizations.
     *
     * @param string $startRule The default start rule to use.
     *
     * @return GrammarInterface $this
     */
    public function finalize($startRule = null);

    /**
     * Returns a string representation of the grammar.
     * Should be as close as possible of the grammar's syntax.
     *
     * @return string
     */
    public function __toString();

    /**
     * Returns a clone of this Grammar.
     *
     * If deep is false, returns a shallow clone.
     * If deep is true, returns a deep clone, with all expressions cloned.
     *
     * @param bool $deep Whether to return a deep clone.
     *
     * @return GrammarInterface
     */
    public function copy($deep = false);
}
