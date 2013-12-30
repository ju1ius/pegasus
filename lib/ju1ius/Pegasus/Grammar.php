<?php

namespace ju1ius\Pegasus;

use ju1ius\Pegasus\Expression;

/**
 * A collection of expressions that describe a language.
 *
 * You can start parsing from the default expression
 * by calling Grammar::parse():
 *
 * $grammar = new Grammar(<<<'EOS'
 * polite_greeting = greeting ", my good sir"
 * greeting = Hi / Hello
 * EOS;
 * );
 * $parse_tree = $grammar->parse('Hello, my good sir');
 *
 * Or start parsing from any of the other expressions.
 * You can pull them out of the grammar as if it were an associative array:
 *
 * $grammar['greeting']->parse('Hi');
 *
 * You could also just construct a bunch of Expression objects yourself
 * and stitch them together into a language, but using a Grammar has some
 * important advantages:
 *
 * - Languages are much easier to define in the nice syntax it provides.
 * - Circular references aren't a pain.
 * - It does all kinds of whizzy space- and time-saving optimizations, like
 *   factoring up repeated subexpressions into a single object,
 *   which should increase cache hit ratio.
 */
class Grammar implements \ArrayAccess, \Countable, \IteratorAggregate
{
    protected
        $rules = [],
        $default_rule = null;

    /**
     * @param string $peg          The grammar definition
     * @param string $default_rule The name of the rule invoked when you call parse() on the grammar.
     *                             Defaults to the first rule.
     **/
    public function __construct($rules, $default_rule=null)
    {
        list($exprs, $first) = $this->expressionsFromSyntax($rules);
        $this->rules = array_merge($this->rules, $exprs);
        $this->default_rule = $default_rule ? $exprs[$default_rule] : $first;
    }

    public function setDefault($name)
    {
        $this->default_rule = $this->rules[$name];
    }
    public function getDefault()
    {
        return $this->default_rule;
    }

    public function parse($syntax)
    {
        return (new Packrat\Parser($this))->parse($syntax);
    }
    
    public function __toString()
    {
        $exprs = [$this->default_rule];
        foreach ($this->rules as $name => $expr) {
            if ($expr === $this->default_rule) continue;
            $exprs[] = $expr;
        }
        return implode("\n", array_map(function($expr) {
            return $expr->asRule();
        }, $exprs));
    }

    public function merge($grammar)
    {
        foreach ($grammar as $name => $rule) {
            $this->rules[$name] = $rule;
        }

        return $this;
    }

    public function offsetExists($index)
    {
        return isset($this->rules[$index]);
    }
    public function offsetGet($index)
    {
        return $this->rules[$index];
    }
    public function offsetSet($index, $value)
    {
        $this->rules[$index] = $value;
    }
    public function offsetUnset($index)
    {
        unset($this->rules[$index]);
    }
    public function count()
    {
        return count($this->rules);
    }
    public function getIterator()
    {
        return new \ArrayIterator($this->rules);
    }

    /**
     * Return a dict of rule names pointing to their expressions.
     *
     * It's a web of expressions, all referencing each other.
     * Typically, there's a single root to the web of references,
     * and that root is the starting symbol for parsing,
     * but there's nothing saying you can't have multiple roots.
     **/
    protected function expressionsFromSyntax($syntax)
    {
        $tree = PegasusGrammar::build()->parse($syntax);
        return (new RuleVisitor)->visit($tree);
    }
}
