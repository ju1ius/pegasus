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
     * The input text.
     *
     * @var string
     */
    protected $text;

    /**
     * The rightmost failure position.
     *
     * @var int
     */
    public $position = 0;

    /**
     * The rightmost failed expression.
     *
     * @var Expression
     */
    public $expr;

    /**
     * The rightmost failed rule.
     *
     * @var string
     */
    public $rule;

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
        $line = $this->line();
        $col = $this->column();
        $text = substr($this->text, $this->position, $this->position + 20);
        return sprintf(
            'ParseError in rule `%s`, expr `%s` on line %s, column %s.'
            ."\n%s"
            . "\n%s",
            $this->rule,
            (string)$this->expr,
            $line, $col,
            $text,
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
