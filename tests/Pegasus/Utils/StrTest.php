<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Tests\Utils;

use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\Utils\Str;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class StrTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getTestClassNameProvider
     *
     * @param object|string $input
     * @param string $expected
     */
    public function testClassName($input, $expected)
    {
        $this->assertSame($expected, Str::className($input));
    }

    public function getTestClassNameProvider()
    {
        return [
            'A FQCN as a string' => [
                'Acme\Demo\FooBar',
                'FooBar'
            ],
            'A FQCN in top-level namespace as a string' => [
                '\FooBar',
                'FooBar'
            ],
            'A FQCN in top-level namespace (without leading backslash) as a string' => [
                'stdClass',
                'stdClass'
            ],
            'An stdClass instance' => [
                new \stdClass(),
                'stdClass'
            ],
            'An Expression instance' => [
                new Literal('foo'),
                'Literal'
            ],
        ];
    }
}
