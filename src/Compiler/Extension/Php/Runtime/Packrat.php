<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Compiler\Extension\Php\Runtime;

use ju1ius\Pegasus\CST\Node;

/**
 * A packrat parser implementing Wrath, Douglass & Millstein's algorithm
 * to prevent infinite loops in left-recursive rules.
 *
 * For a full implementation of left-recursion, use LeftRecursiveParser.
 *
 * @see doc/algo/packrat-lr.pdf
 */
class Packrat extends Parser
{
    /**
     * @var array
     */
    protected $memo = [];

    protected function beforeParse()
    {
        $this->memo = [];
    }

    protected function afterParse($result)
    {
        $this->memo = [];
    }

    /**
     * @inheritdoc
     */
    protected function apply(string $rule)
    {
        $pos = $this->pos;
        $capturing = (int)$this->isCapturing;
        $memo = $this->memo[$capturing][$pos][$rule] ?? null;

        if ($memo) {
            $this->pos = $memo->end;

            return $memo->result;
        }

        // Store a result of FAIL in the memo table before it evaluates the body of a rule.
        // This has the effect of making all left-recursive applications (both direct and indirect) fail.
        $memo = new MemoEntry(null, $pos);
        $this->memo[$capturing][$pos][$rule] = $memo;
        // evaluate expression
        $result = $this->matchers[$rule]();
        // update the result in the memo table
        $memo->result = $result;
        $memo->end = $this->pos;

        return $result;
    }
}
