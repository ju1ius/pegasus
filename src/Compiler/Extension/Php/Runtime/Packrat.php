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

/**
 * A packrat parser implementing Wrath, Douglass & Millstein's algorithm
 * to prevent infinite loops in left-recursive rules.
 *
 * For a full implementation of left-recursion, use LeftRecursiveParser.
 *
 * @see doc/algo/packrat-lr.pdf
 */
class Packrat extends RecursiveDescent
{
    /**
     * @var array
     */
    protected $memo = [];

    protected function beforeParse()
    {
        parent::beforeParse();
        $this->memo = [
            false => [],
            true => [],
        ];
    }

    protected function afterParse($result)
    {
        parent::afterParse($result);
        $this->memo = [];
    }

    protected function cut(int $position)
    {
        $this->cutStack->pop();
        $this->cutStack->push(true);
        // clear memo entries for previous positions
        foreach ($this->memo as $capturing => $table) {
            foreach ($table as $pos => $rules) {
                if ($pos < $position) {
                    unset($this->memo[$capturing][$pos]);
                }
            }
        }
    }
}
