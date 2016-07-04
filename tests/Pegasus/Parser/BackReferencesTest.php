<?php

namespace ju1ius\Pegasus\Tests\Parser;

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Builder;
use ju1ius\Pegasus\Parser\Packrat as Parser;
use ju1ius\Pegasus\Tests\PegasusTestCase;

class BackReferencesTest extends PegasusTestCase
{
    /**
     * @dataProvider testLiteralReferencesProvider
     */
    public function testLiteralReferences(Grammar $grammar, $input, $expected)
    {
        $result = (new Parser($grammar))->parseAll($input);
        $this->assertEquals($expected, $result->getText());
    }

    public function testLiteralReferencesProvider()
    {
        return [
            'labeled reference in same rule' => [
                Builder::create()->rule('start')->seq()
                    ->label('a')->literal('foo')
                    ->literal('bar')
                    ->literal('${a}')
                    ->getGrammar(),
                'foobarfoo',
                'foobarfoo',
            ],
        ];
    }
}
