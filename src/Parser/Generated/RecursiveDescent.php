<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace ju1ius\Pegasus\Parser\Generated;

use ju1ius\Pegasus\Exception\ParseError;
use ju1ius\Pegasus\Exception\IncompleteParseError;


class RecursiveDescent implements ParserInterface
{
    protected $text = null;
    public $pos = 0;
    protected $error = null;

    /**
     * Return the parse tree matching this expression at the given position,
     * not necessarily extending all the way to the end of $text.
     *
     * @throw ParseError if there's no match there
     *
     * @return Node
     */
    public function parseAll($text, $rule=null)
    {
        $result = $this->parse($text, 0, $rule);
        if ($this->pos < strlen($text)) {
            //echo $result->inspect(), "\n";
            throw new IncompleteParseError(
                $text,
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
    public function parse($text, $pos=0, $rule=null)
    {
        $this->text = $text;
        $this->pos = $pos;
        $this->error = new ParseError($text);
        $this->refmap = [];

        //FIXME: how to do this ?
        // maybe write a generator that recursively yields subexpressions ?
        // it would need to yield depth-first, ie terminal rules,
        // then parent composite rules, etc...
        // ATM we just pass $this to the Expression::match method,
        // and let expressions call $parser->apply for their children.
        $result = $this->apply($rule, $pos);

        if (!$result) {
            throw $this->error;
        }

        return $result;
    }

    public function apply($rule_name, $pos=0)
    {
        $this->pos = $pos;
        $this->error->pos = $pos;
        $this->error->expr = $rule_name;

        // evaluate expression
        $result = $this->evaluate($rule_name);

        return $result;
    }

    /**
     * Evaluates an expression & updates current position on success.
     *
     */
    public function evaluate($rule_name)
    {
        $match_method = "match_{$rule_name}";
        $result = $this->$rule_name();
        if ($result) {
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
        if (!isset($this->refmap[$name])) return '';
        list($id, $pos) = $this->refmap[$name];
        $memo = $this->memo[$id][$pos];
        return (string) $memo->result;
    }
}
