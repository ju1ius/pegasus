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
class ParseError extends \RuntimeException
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
        $length = strlen($this->text);
        $line = $this->line();
        $col = $this->column($length);
        $text = $this->getTextExtract($col, $length);

        return sprintf(
            'ParseError: expression `%s` in rule `%s` failed to match on line %s, column %s.'
            . "\n%s",
            (string)$this->expr,
            $this->rule,
            $line, $col,
            $text
        );
    }

    protected function getTextExtract($column, $length)
    {
        $line = $this->getLineText($length);
        return sprintf("%s\n%s⬑", $line, str_repeat(' ', $column - 1));
    }

    protected function getLineText($length = null)
    {
        if ($length === null) {
            $length = strlen($this->text);
        }
        $bol = $this->bol($length);
        $eol = $this->eol($bol, $length);

        return substr($this->text, $bol + 1, $eol - $bol);
    }

    public function line()
    {
        return $this->position
            ? substr_count($this->text, "\n", 0, $this->position) + 1
            : 1;
    }

    public function column($length = null)
    {
        if (!$this->position) {
            return 1;
        }
        $bol = $this->bol($length);

        return $this->position - $bol;
    }

    /**
     * Returns the byte offset of the beginning of the line of the current error position.
     *
     * @param int $length
     *
     * @return int
     */
    protected function bol($length = null)
    {
        if ($length === null) {
            $length = strlen($this->text);
        }
        $bol = strrpos($this->text, "\n", -($length - $this->position));
        if ($bol === false) {
            return 0;
        }

        return $bol;
    }

    /**
     * Returns the byte offset of the end of the line of the current error position.
     *
     * @param int $bol
     * @param int $length
     *
     * @return int
     */
    protected function eol($bol = null, $length = null)
    {
        if ($length === null) {
            $length = strlen($this->text);
        }
        if ($bol === null) {
            $bol = $this->bol($length);
        }
        $eol = strpos($this->text, "\n", $bol + 1);
        if ($eol === false) {
            $eol = $length;
        }

        return $eol;
    }
}
