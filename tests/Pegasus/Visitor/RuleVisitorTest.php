<?php

namespace ju1ius\Pegasus\Tests\Visitor;

use ju1ius\Pegasus\Tests\PegasusTestCase;

use ju1ius\Pegasus\Grammar;


class RuleVisitorTest extends PegasusTestCase
{
    public function testDefaultRuleIsNotAReference()
    {
        $s = <<<'EOS'
x = y
y = 'foo'
EOS;
        $g = Grammar::fromSyntax($s);
        $this->assertNotInstanceOf(
            'ju1ius\Pegasus\Expression\Reference',
            $g->getStartRule()
        );
    }

}
