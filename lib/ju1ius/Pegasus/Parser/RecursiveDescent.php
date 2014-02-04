<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace ju1ius\Pegasus\Parser;

use ju1ius\Pegasus\Exception\ParseError;
use ju1ius\Pegasus\Exception\IncompleteParseError;
use ju1ius\Pegasus\GrammarInterface;
use ju1ius\Pegasus\Expression;


class RecursiveDescent implements ParserInterface
{
    protected $grammar = null;
    protected $source = null;
    public $pos = 0;
    protected $error = null;

    public function __construct(GrammarInterface $grammar)
    {
        $this->grammar = $grammar;
    }

    /**
     * Return the parse tree matching this expression at the given position,
     * not necessarily extending all the way to the end of $text.
     *
     * @throw ParseError if there's no match there
     *
     * @return Node
     */
    public function parseAll($source, $rule=null)
    {
        $result = $this->parse($source, 0, $rule);
        if ($this->pos < strlen($source)) {
            echo $result->inspect(), "\n";
            throw new IncompleteParseError(
                $source,
                $this->pos,
                $this->error->expr
            );
        }

        return $result;
    }

    /**
     * Return the parse tree matching this expression at the given position,
     * not necessarily extending all the way to the end of $text.
     *
     * @throw ParseError if there's no match there
     *
     * @return Node | null
     */
    public function parse($source, $pos=0, $rule=null)
    {
        $this->source = $source;
        $this->pos = $pos;
        $this->error = new ParseError($source);
        $this->refmap = [];
        $this->labels = [];
        // fold the grammar
        $was_folded = $this->grammar->isFolded();
        if (!$was_folded) {
            $this->grammar->fold();
        }

        if (!$rule) {
            $rule = $this->grammar->getStartRule();
        } else {
            $rule = $this->grammar[$rule];
        }

        //FIXME: how to do this ?
        // maybe write a generator that recursively yields subexpressions ?
        // it would need to yield depth-first, ie terminal rules,
        // then parent composite rules, etc...
        // ATM we just pass $this to the Expression::match method,
        // and let expressions call $parser->apply for their children.
        $result = $this->apply($rule, $pos);

        if (!$was_folded) {
            // grammar wasn't folded before parsing, so we unfold it
            // to restore it's original state.
            $this->grammar->unfold();
        }

        if (!$result) {
            throw $this->error;
        }

        return $result;
    }

    public function apply(Expression $expr, $pos=0)
    {
        $this->pos = $pos;
        $this->error->pos = $pos;
        $this->error->expr = $expr;

        // evaluate expression
        $result = $this->evaluate($expr);

        return $result;
    }

    /**
     * Evaluates an expression & updates current position on success.
     *
     */
    public function evaluate(Expression $expr)
    {
        $result = $expr->match($this->source, $this->pos, $this);
        if ($result) {
            // store labels and named expressions for backreferences
            if ($expr instanceof Expression\Label) {
                $this->labels[$expr->label] = $result;
            }
            if ($expr->name) {
                $this->refmap[$expr->name] = $result;
            }
            // update parser position
            $this->pos = $result->end;
            $this->error->node = $result;
        }
        return $result;
    }

    /**
     * Search the references map for an expression with the same name.
     *
     */
    public function getReference($name)
    {
        if (isset($this->labels[$name])) {
            return (string) $this->labels[$name]; 
        } elseif (isset($this->refmap[$name])) {
            return (string) $this->refmap[$name]; 
        }
        return '';
    }
}
