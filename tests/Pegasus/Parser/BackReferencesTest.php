<?php

namespace ju1ius\Pegasus\Tests\Parser;

use ju1ius\Pegasus\Tests\PegasusTestCase;

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Parser\Packrat as Parser;


class BackReferencesTest extends PegasusTestCase
{
    /**
     * @dataProvider testLiteralReferencesProvider
     */
    public function testLiteralReferences($syntax, $input, $expected)
    {
        $grammar = Grammar::fromSyntax($syntax);
        $result = (new Parser($grammar))->parseAll($input);
        $this->assertEquals($expected, $result->getText());
    }
    public function testLiteralReferencesProvider()
    {
        return [
            [
                'start <- a:"foo" "bar" "${a}"',
                'foobarfoo',
                'foobarfoo',
            ]
        ];
    }
}
