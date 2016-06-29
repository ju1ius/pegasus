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
use ju1ius\Pegasus\Node;

class RecursiveDescent implements ParserInterface
{
    /**
     * @var string
     */
    protected $text;

    /**
     * @var int
     */
    public $pos = 0;

    /**
     * @var ParseError
     */
    protected $error;

    /**
     * @var
     */
    protected $refmap;

    /**
     * Return the parse tree matching this expression at the given position,
     * not necessarily extending all the way to the end of $text.
     *
     * @throw ParseError if there's no match there
     *
     * @param string $text
     * @param string $rule
     *
     * @return Node
     * @throws IncompleteParseError
     * @throws ParseError
     * @throws null
     */
    public function parseAll($text, $rule = null)
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
     * @param string $text
     * @param int    $pos
     * @param string $rule
     *
     * @return Node|null
     * @throws ParseError
     * @throws null
     */
    public function parse($text, $pos = 0, $rule = null)
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

    public function apply($ruleName, $pos = 0)
    {
        $this->pos = $pos;
        $this->error->position = $pos;
        $this->error->expr = $ruleName;

        // evaluate expression
        $result = $this->evaluate($ruleName);

        return $result;
    }

    /**
     * Evaluates an expression & updates current position on success.
     *
     */
    public function evaluate($ruleName)
    {
        $match_method = "match_{$ruleName}";
        $result = $this->{$ruleName}();
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
        if (!isset($this->refmap[$name])) {
            return '';
        }
        list($id, $pos) = $this->refmap[$name];
        $memo = $this->memo[$id][$pos];

        return (string)$memo->result;
    }
}
