<?php

use ju1ius\Test\Pegasus\PegasusTestCase;

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
