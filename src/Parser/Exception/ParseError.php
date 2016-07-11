<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Parser\Exception;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Node;

/**
 * A call to Expression::parse() or match() didn't match.
 */
class ParseError extends \Exception
{
    /**
     * @var string
     */
    protected $text;

    /**
     * @var int
     */
    public $position = 0;

    /**
     * @var Expression
     */
    public $expr;

    /**
     * @var string
     */
    public $rule;

    /**
     * @var Node
     */
    public $node;


    public function __construct($text, $pos = 0, $expr = null, $rule = '')
    {
        $this->text = $text;
        $this->position = $pos;
        $this->expr = $expr;
        $this->rule = $rule;

        parent::__construct();
    }

    public function __toString()
    {
        $ruleName = $this->rule ?: $this->expr->name;

        return sprintf(
            '%s: rule <%s%s> didn\'t match on line %s, column %s ("%s").'
            . "\n%s",
            __CLASS__,
            $ruleName ? sprintf('%s = ', $ruleName) : '',
            (string)$this->expr,
            $this->line(),
            $this->column(),
            substr($this->text, $this->position, $this->position + 20),
            $this->getTraceAsString()
        );
    }

    public function line()
    {
        return $this->position
            ? substr_count($this->text, "\n", 0, $this->position) + 1
            : 1;
    }

    public function column()
    {
        if (!$this->position) {
            return 1;
        }
        $i = strrpos($this->text, "\n", -(strlen($this->text) - $this->position));

        return $i === false ? $this->position + 1 : $this->position - $i;
    }
}