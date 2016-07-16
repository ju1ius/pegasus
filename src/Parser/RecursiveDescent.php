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

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Parser\Exception\ParseError;

class RecursiveDescent extends Parser
{
    /**
     * @inheritdoc
     */
    public function parse($source, $pos = 0, $startRule = null)
    {
        $this->source = $source;
        $this->pos = $pos;
        $this->error = new ParseError($source);
        $this->isCapturing = true;
        $this->applicationStack = new \SplStack();
        $rule = $startRule ?: $this->grammar->getStartRule()->name;

        gc_disable();
        $result = $this->apply($rule, Scope::void());
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
    public function apply($rule, Scope $scope, $super = false)
    {
        $expr = $super ? $this->grammar->super($rule) : $this->grammar[$rule];

        return $this->evaluate($expr, $scope);
    }
}
