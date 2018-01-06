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


class RecursiveDescent extends Parser
{
    /**
     * @inheritdoc
     */
    public function apply(string $rule, bool $super = false)
    {
        $expr = $super ? $this->grammar->super($rule) : $this->grammar[$rule];

        return $this->evaluate($expr);
    }
}
