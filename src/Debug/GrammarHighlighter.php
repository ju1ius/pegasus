<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Debug;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Traverser\GrammarTraverser;
use ju1ius\Pegasus\Visitor\GrammarVisitor;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class GrammarHighlighter extends GrammarVisitor
{
    /**
     * @var string
     */
    private $output;

    /**
     * @var ExpressionHighlighter
     */
    private $highlighter;

    public function __construct()
    {
        $this->highlighter = new ExpressionHighlighter();
    }

    /**
     * @param Grammar $grammar
     *
     * @return string
     */
    public static function highlight(Grammar $grammar)
    {
        (new GrammarTraverser(false))
            ->addVisitor($highlighter = new self())
            ->traverse($grammar);

        return $highlighter->getOutput();
    }

    /**
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @inheritDoc
     */
    public function beforeTraverse(Grammar $grammar)
    {
        $this->output = '';
    }

    /**
     * @inheritDoc
     */
    public function enterRule(Grammar $grammar, Expression $expr)
    {
        $this->output .= sprintf(
            '<rule>%s</rule> <d>=</d> ',
            $expr->name
        );
        $this->highlighter->beforeTraverse($expr);
    }

    /**
     * @inheritDoc
     */
    public function leaveRule(Grammar $grammar, Expression $expr)
    {
        $this->highlighter->afterTraverse($expr);
        $this->output .= $this->highlighter->getOutput() . "\n\n";
    }

    /**
     * @inheritDoc
     */
    public function enterExpression(Grammar $grammar, Expression $expr, Composite $parent = null, $index = null)
    {
        $this->highlighter->enterExpression($expr, $index);
    }

    /**
     * @inheritDoc
     */
    public function leaveExpression(Grammar $grammar, Expression $expr, Composite $parent = null, $index = null)
    {
        $this->highlighter->leaveExpression($expr, $index);
    }
}
