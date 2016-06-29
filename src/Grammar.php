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

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Grammar\Exception\AnonymousTopLevelExpression;
use ju1ius\Pegasus\Grammar\Exception\GrammarException;
use ju1ius\Pegasus\Grammar\Exception\MissingStartRule;
use ju1ius\Pegasus\Grammar\Exception\RuleNotFound;
use ju1ius\Pegasus\Parser\LRPackrat as Parser;
use ju1ius\Pegasus\Traverser\GrammarTraverser;
use ju1ius\Pegasus\Visitor\GrammarVisitor;
use ju1ius\Pegasus\Visitor\MetaGrammarNodeVisitor;

/**
 * A collection of expressions that describe a language.
 *
 * <code>
 * use ju1ius\Pegasus\Grammar;
 * use ju1ius\Pegasus\Parser\Packrat as Parser;
 * // or if the grammar is left-recursive:
 * // use ju1ius\Pegasus\Parser\LRPackrat as Parser
 *
 * $syntax = <<<'EOS'
 * polite_greeting = greeting ", my good sir"
 * greeting = Hi / Hello
 * EOS;
 *
 * $grammar = Grammar::fromSyntax($syntax);
 * $parse_tree = (new Parser($grammar))->parseAll('Hello, my good sir');
 * </code>
 *
 * Or start parsing from any of the other expressions.
 *
 * <code>
 * $parse_tree = (new Parser($grammar))->parseAll('Hi', 'greeting');
 * </code>
 *
 * You can also just construct a bunch of Expression objects yourself
 * and stitch them together into a language by using:
 * <code>
 * Grammar::fromExpression($my_expression);
 * </code>
 * But using a Grammar has some important advantages:
 *
 */
class Grammar implements GrammarInterface
{
    /**
     * @var Expression[] The rules used by this Grammar.
     */
    protected $rules = [];

    /**
     * @var string The default start rule of the grammar.
     */
    protected $defaultRule = null;

    /**
     * @var bool True if the grammar is in folded state.
     */
    protected $folded = true;

    /**
     * @var string The name of the grammar
     */
    protected $name = '';

    /**
     * Factory method that constructs a Grammar object from an associative array of rules.
     *
     * @param Expression[] $rules      An array of ['rule_name' => $expression].
     * @param Expression   $start_rule The top level expression of this grammar.
     *
     * @return Grammar
     */
    public static function fromArray(array $rules, $start_rule = null)
    {
        $grammar = new static();
        foreach ($rules as $name => $rule) {
            $grammar[$name] = $rule;
        }
        if ($start_rule) {
            $grammar->setStartRule($start_rule);
        }

        return $grammar->unfold();
    }

    /**
     * Factory method that constructs a Grammar object from a syntax string.
     *
     * @param string $syntax
     * @param string $startRule Optional start rule name for the grammar.
     *
     * @return Grammar
     */
    public static function fromSyntax($syntax, $startRule = null)
    {
        $metagrammar = MetaGrammar::create();
        $tree = (new Parser($metagrammar))->parseAll($syntax);
        $grammar = (new MetaGrammarNodeVisitor)->visit($tree);
        if ($startRule) {
            $grammar->setStartRule($startRule);
        }

        return $grammar->unfold();
    }

    /**
     * Factory method that constructs a Grammar object from an Expression.
     *
     * @param Expression $expr      The expression to build the grammar from.
     * @param string     $startRule Optional start rule name for the grammar.
     *
     * @return Grammar
     * @throws \ju1ius\Pegasus\Grammar\Exception\GrammarException
     */
    public static function fromExpression(Expression $expr, $startRule = null)
    {
        if (!$startRule && !$expr->name) {
            throw new AnonymousTopLevelExpression($expr);
        }
        if (!$startRule) {
            $startRule = $expr->name;
        }

        $grammar = new static();
        $grammar[$startRule] = $expr;

        return $grammar->unfold();
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @inheritDoc
     */
    public function setStartRule($name)
    {
        if (isset($this->rules[$name])) {
            $this->defaultRule = $name;

            return $this;
        }
        throw new RuleNotFound($name);
    }

    /**
     * @inheritDoc
     */
    public function getStartRule()
    {
        if (!$this->defaultRule) {
            throw new MissingStartRule();
        }

        return $this->rules[$this->defaultRule];
    }

    /**
     * @inheritDoc
     */
    public function isFolded()
    {
        return $this->folded;
    }

    /**
     * @inheritDoc
     */
    public function fold($startRule = null)
    {
        $traverser = (new GrammarTraverser(false, true))
            ->addVisitor(new GrammarVisitor);
        $traverser->traverse($this);

        if ($startRule) {
            $this->setStartRule($startRule);
        }

        $this->folded = true;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function unfold()
    {
        $traverser = (new GrammarTraverser(false, false))
            ->addVisitor(new GrammarVisitor);
        $traverser->traverse($this);

        $this->folded = false;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function finalize($startRule = null)
    {
        $this->fold($startRule);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function copy($deep = false)
    {
        $clone = clone $this;
        if ($deep) {
            $traverser = (new GrammarTraverser(true, $this->isFolded()))
                ->addVisitor(new GrammarVisitor);
            $traverser->traverse($clone);
        }

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function merge(GrammarInterface $other)
    {
        $new = $this->copy(true);
        $other = $other->copy(true);

        foreach ($other as $name => $rule) {
            $new[$name] = $rule;
        }

        return $new->unfold();
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        $out = '';
        if ($name = $this->getName()) {
            $out .= "%name $name\n";
        }
        $start_rule = $this->getStartRule();
        $out .= "%start {$this->defaultRule}\n";

        $out .= "\n";
        foreach ($this->rules as $name => $expr) {
            $out .= sprintf("%s = %s\n", $name, $expr->asRightHandSide());
        }

        return $out;
    }

    public function offsetExists($name)
    {
        return isset($this->rules[$name]);
    }

    public function offsetGet($name)
    {
        return $this->rules[$name];
    }

    public function offsetSet($name, $expr)
    {
        if (!$expr instanceof Expression) {
            throw new \InvalidArgumentException(sprintf(
                'Value passed to `%s` must be instance of ju1ius\Pegasus\Expression, `%s` given.',
                __METHOD__,
                is_object($expr) ? get_class($expr) : gettype($expr)
            ));
        }

        $expr->name = $name;

        if (!$this->defaultRule) {
            $this->defaultRule = $name;
        }

        $this->rules[$name] = $expr;
    }

    public function offsetUnset($name)
    {
        unset($this->rules[$name]);
    }

    public function count()
    {
        return count($this->rules);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->rules);
    }
}
