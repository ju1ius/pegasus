<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Parser;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Parser\Exception\ParseError;

class RecursiveDescent extends Parser
{
    /**
     * @inheritdoc
     */
    public function parse(string $text, int $pos = 0, ?string $startRule = null)
    {
        $this->source = $text;
        $this->pos = $pos;
        $this->bindings = [];
        $this->error = new ParseError($text);
        $this->isCapturing = true;
        $this->applicationStack = new \SplStack();
        $rule = $startRule ?: $this->grammar->getStartRule();

        gc_disable();
        $result = $this->apply($rule);
        gc_enable();

        if (!$result) {
            throw $this->error;
        }
        $this->applicationStack = null;

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function apply(string $rule, bool $super = false)
    {
        $expr = $super ? $this->grammar->super($rule) : $this->grammar[$rule];

        return $this->evaluate($expr);
    }
}
