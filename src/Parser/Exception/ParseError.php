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
use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Utils\SourceExcerpt;

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
     * @var Expression
     */
    public $rule;

    /**
     * @var SourceExcerpt
     */
    protected $sourceExcerpt;

    public function __construct($text, $pos = 0, $expr = null, $rule = '')
    {
        $this->text = $text;
        $this->position = $pos;
        $this->expr = $expr;
        $this->rule = $rule;
        $this->sourceExcerpt = new SourceExcerpt($text, 3);

        parent::__construct();
    }

    public function __toString()
    {
        return sprintf(
            "ParseError: in rule `%s`, expression `%s`,\n%s",
            $this->rule,
            (string)$this->expr,
            $this->sourceExcerpt->getExcerpt($this->position)
        );
    }
}
