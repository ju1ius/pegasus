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
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class GrammarHighlighter extends GrammarVisitor
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var ExpressionHighlighter
     */
    private $highlighter;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
        $this->highlighter = new ExpressionHighlighter($output);
    }

    /**
     * @param Grammar         $grammar
     * @param OutputInterface $output
     *
     * @return string
     */
    public static function highlight(Grammar $grammar, OutputInterface $output)
    {
        (new GrammarTraverser(false))
            ->addVisitor($highlighter = new self($output))
            ->traverse($grammar);
    }

    /**
     * @inheritDoc
     */
    public function beforeTraverse(Grammar $grammar)
    {
        if ($name = $grammar->getName()) {
            $this->output->writeln(sprintf(
                '<directive>%%name</directive> <class>%s</class>',
                $name
            ));
        }
        $this->output->writeln(sprintf(
            '<directive>%%start</directive> <rule>%s</rule>',
            $grammar->getStartRule()->name
        ));
        $this->output->writeln('');
    }

    /**
     * @inheritDoc
     */
    public function enterRule(Grammar $grammar, Expression $expr)
    {
        if ($grammar->isInlined($expr->name)) {
            $this->output->write(sprintf('<directive>%%sinline</directive> '));
        }
        $this->output->write(sprintf(
            '<rule>%s</rule> <d>=</d> ',
            $expr->name
        ));
        $this->highlighter->beforeTraverse($expr);
    }

    /**
     * @inheritDoc
     */
    public function leaveRule(Grammar $grammar, Expression $expr)
    {
        $this->highlighter->afterTraverse($expr);
        $this->output->writeln(['', '']);
    }

    /**
     * @inheritDoc
     */
    public function enterExpression(Grammar $grammar, Expression $expr, Composite $parent = null, $index = null)
    {
        $this->highlighter->enterExpression($expr, $parent, $index);
    }

    /**
     * @inheritDoc
     */
    public function leaveExpression(Grammar $grammar, Expression $expr, Composite $parent = null, $index = null)
    {
        $this->highlighter->leaveExpression($expr, $parent, $index);
    }
}
