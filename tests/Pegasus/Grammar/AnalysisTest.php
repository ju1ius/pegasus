<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Tests\Grammar;

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Analysis;
use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\Tests\PegasusTestCase;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class AnalysisTest extends PegasusTestCase
{
    /**
     * @dataProvider getTestCanModifyBindingsProvider
     *
     * @param Grammar $grammar
     * @param bool    $expected
     */
    public function testCanModifyBindings(Grammar $grammar, $expected)
    {
        $analysis = new Analysis($grammar);
        $this->assertSame($expected, $analysis->canModifyBindings('test'));
    }

    public function getTestCanModifyBindingsProvider()
    {
        return [
            'No label' => [
                GrammarBuilder::create()->rule('test')->sequence()
                    ->literal('foo')
                    ->literal('bar')
                    ->getGrammar(),
                false
            ],
            'Top-level label' => [
                GrammarBuilder::create()->rule('test')->sequence()
                    ->label('foo')->literal('foo')
                    ->skip()->match('\s*')
                    ->backReference('foo')
                    ->getGrammar(),
                true
            ],
            'Skipped label' => [
                GrammarBuilder::create()->rule('test')->sequence()
                    ->skip()->label('foo')->literal('foo')
                    ->backReference('foo')
                    ->getGrammar(),
                true
            ]
        ];
    }

    /**
     * @dataProvider getTestIsRecursiveProvider
     *
     * @param Grammar $grammar
     * @param string  $rule
     * @param bool    $expected
     */
    public function testIsRecursive(Grammar $grammar, $rule, $expected)
    {
        $analysis = new Analysis($grammar);
        $this->assertSame($expected, $analysis->isRecursive($rule));
        $this->assertSame(!$expected, $analysis->isRegular($rule));
    }

    public function getTestIsRecursiveProvider()
    {
        return [
            'Directly recursive rule' => [
                \ju1ius\Pegasus\GrammarBuilder::create()->rule('a')->sequence()
                    ->literal('a')
                    ->ref('a')
                    ->getGrammar(),
                'a',
                true
            ],
            'Indirectly recursive rule' => [
                GrammarBuilder::create()
                    ->rule('a')->sequence()
                        ->literal('a')
                        ->ref('b')
                    ->rule('b')->sequence()
                        ->literal('b')
                        ->ref('c')
                    ->rule('c')->sequence()
                        ->literal('c')
                        ->ref('a')
                    ->getGrammar(),
                'a',
                true
            ],
            'Non-recursive rule' => [
                GrammarBuilder::create()
                    ->rule('a')->sequence()
                        ->literal('a')
                        ->ref('b')
                    ->rule('b')->sequence()
                        ->literal('b')
                        ->ref('c')
                    ->rule('c')->sequence()
                        ->literal('c')
                        ->ref('b')
                    ->getGrammar(),
                'a',
                false
            ],
            'A rule with a super call' => [
                GrammarBuilder::create()->rule('a')->sequence()
                    ->literal('a')
                    ->super('a')
                    ->getGrammar(),
                'a',
                true
            ],
        ];
    }

    /**
     * @dataProvider getTestLeftIsRecursiveProvider
     *
     * @param Grammar $grammar
     * @param string  $rule
     * @param bool    $expected
     */
    public function testIsLeftRecursive(Grammar $grammar, $rule, $expected)
    {
        $analysis = new Analysis($grammar);
        $this->assertSame($expected, $analysis->isLeftRecursive($rule));
    }

    public function getTestLeftIsRecursiveProvider()
    {
        return [
            'Directly left-recursive rule' => [
                GrammarBuilder::create()->rule('a')->oneOf()
                    ->ref('a')
                    ->literal('a')
                    ->getGrammar(),
                'a',
                true
            ],
            'Indirectly left-recursive rule' => [
                GrammarBuilder::create()
                    ->rule('a')->ref('b')
                    ->rule('b')->ref('c')
                    ->rule('c')->ref('a')
                    ->getGrammar(),
                'a',
                true
            ],
            'Non-recursive rule' => [
                GrammarBuilder::create()
                    ->rule('a')->ref('b')
                    ->rule('b')->ref('c')
                    ->rule('c')->ref('b')
                    ->getGrammar(),
                'a',
                false
            ],
            'Recursive but not left-recursive rule' => [
                GrammarBuilder::create()->rule('a')->sequence()
                    ->literal('a')
                    ->ref('a')
                    ->getGrammar(),
                'a',
                false
            ]
        ];
    }

    public function testIsReferenced()
    {
        $grammar = GrammarBuilder::create()
            ->rule('foobarbaz')->oneOf()
                ->ref('foobarbaz')
                ->ref('foobar')
                ->ref('baz')
            ->rule('foobar')->sequence()
                ->ref('foo')
                ->ref('bar')
            ->rule('foo')->literal('foo')
            ->rule('bar')->literal('bar')
            ->rule('baz')->literal('baz')
            ->rule('lonely')->literal('nope')
            ->getGrammar();
        $analysis = new Analysis($grammar);

        $this->assertFalse($analysis->isReferenced('lonely'));
        $this->assertTrue($analysis->isReferenced('bar'));
    }

    public function testGetReferencesFrom()
    {
        $grammar = GrammarBuilder::create()
            ->rule('foobarbaz')->oneOf()
                ->ref('foobarbaz')
                ->ref('foobar')
                ->ref('baz')
            ->rule('foobar')->sequence()
                ->ref('foo')
                ->ref('bar')
            ->rule('foo')->literal('foo')
            ->rule('bar')->literal('bar')
            ->rule('baz')->literal('baz')
            ->getGrammar();
        $analysis = new Analysis($grammar);

        $expected = ['foobarbaz', 'foobar', 'foo', 'bar', 'baz'];
        $this->assertEquals($expected, $analysis->getReferencesFrom('foobarbaz'));

        $expected = ['foo', 'bar'];
        $this->assertEquals($expected, $analysis->getReferencesFrom('foobar'));

        $this->assertEquals([], $analysis->getReferencesFrom('foo'));
    }

    public function testGetLeftReferencesFrom()
    {
        $grammar = GrammarBuilder::create()
            ->rule('xs')->oneOf()
                ->sequence()
                    ->ref('xs')
                    ->ref('x')
                ->end()
                ->ref('x')
            ->rule('x')->literal('x')
            ->getGrammar();
        $analysis = new Analysis($grammar);

        $expected = ['xs', 'x'];
        $this->assertEquals($expected, $analysis->getLeftReferencesFrom('xs'));
    }
}
