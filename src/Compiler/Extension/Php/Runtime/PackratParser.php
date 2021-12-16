<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Compiler\Extension\Php\Runtime;

/**
 * A packrat parser implementing Wrath, Douglass & Millstein's algorithm
 * to prevent infinite loops in left-recursive rules.
 *
 * For a full implementation of left-recursion, use LeftRecursiveParser.
 *
 * @see doc/algo/packrat-lr.pdf
 */
class PackratParser extends RecursiveDescentParser
{
    protected array $memo = [];

    protected function beforeParse(): void
    {
        parent::beforeParse();
        $this->memo = [
            false => [],
            true => [],
        ];
    }

    protected function afterParse($result): void
    {
        parent::afterParse($result);
        $this->memo = [];
    }

    protected function cut(int $position): void
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
