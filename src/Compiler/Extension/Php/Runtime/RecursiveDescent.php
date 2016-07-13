<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Compiler\Extension\Php\Runtime;

use ju1ius\Pegasus\Node;

class RecursiveDescent extends Parser
{
    /**
     * @inheritdoc
     */
    protected function apply($ruleName, $super = false)
    {
        return $this->matchers[$ruleName]();
    }
}
