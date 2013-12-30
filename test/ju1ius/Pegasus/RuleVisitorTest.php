<?php

require_once __DIR__.'/../../Pegasus_TestCase.php';

use ju1ius\Pegasus\Grammar;


class RuleVisitorTest extends Pegasus_TestCase
{
    public function testDefaultRuleIsNotAReference()
    {
        $s = <<<'EOS'
x = y
y = 'foo'
EOS;
        $g = new Grammar($s);
        $this->assertNotInstanceOf(
            'ju1ius\Pegasus\Expression\LazyReference',
            $g->getDefault()
        );
    }
    
}
