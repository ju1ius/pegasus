<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace ju1ius\Pegasus\Debug;

use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Traverser\ExpressionTraverser;
use ju1ius\Pegasus\Traverser\GrammarTraverser;
use ju1ius\Pegasus\Visitor\GrammarVisitor;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Expression;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Visitor that dumps a grammar's rules & expression tree.
 */
final class GrammarDumper extends GrammarVisitor
{
    /**
     * @var array
     */
    private $indentStack;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * GrammarDumper constructor.
     *
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @param Grammar         $grammar
     * @param OutputInterface $output
     *
     * @throws Grammar\Exception\SelfReferencingRule
     */
    public static function dump(Grammar $grammar, OutputInterface $output)
    {
        (new GrammarTraverser(false, false))
            ->addVisitor(new self($output))
            ->traverse($grammar);
    }

    /**
     * @inheritdoc
     */
    public function enterRule(Grammar $grammar, Expression $expr)
    {
        $this->indentStack = [];
        $this->output->write(sprintf(
            '<class>Rule</class> <rule>%s</rule> <d>=</d> ',
            $expr->getName()
        ));
        ExpressionHighlighter::highlight($expr, $this->output);
        $this->output->writeln('');
    }

    /**
     * @inheritdoc
     */
    public function enterExpression(Grammar $grammar, Expression $expr, $index = null, $isLast = false)
    {
        $indent = implode('', $this->indentStack);
        $indent .= $isLast ? '└ ' : '├ ';

        $this->output->write(sprintf(
            '<d>%s</d><class>%s</class> ',
            $indent,
            str_replace('ju1ius\Pegasus\Expression\\', '', get_class($expr))
        ));
        ExpressionHighlighter::highlight($expr, $this->output);
        $this->output->writeln('');

        if ($expr instanceof Composite && $index !== null) {
            $this->indentStack[] = $isLast ? '  ' : '│ ';
        }
    }

    /**
     * @inheritdoc
     */
    public function leaveExpression(Grammar $grammar, Expression $expr, $index = null, $isLast = false)
    {
        if ($expr instanceof Composite) {
            array_pop($this->indentStack);
        }
    }
}
