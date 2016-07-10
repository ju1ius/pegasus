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
 * Visitor that dumps a grammar's rules & expression tree to stdout.
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
        $this->output->writeln(sprintf(
            '<class>Rule</class> <rule>%s</rule> <d>=</d> %s',
            $expr->name,
            $this->highlightExpression($expr)
        ));
    }

    /**
     * @inheritdoc
     */
    public function enterExpression(Grammar $grammar, Expression $expr, Composite $parent = null, $index = null)
    {
        $isLast = $index === count($parent) - 1;
        $indent = implode('', $this->indentStack);
        $indent .= $isLast ? '└ ' : '├ ';

        $this->output->writeln(sprintf(
            "<d>%s</d><class>%s</class> %s",
            $indent,
            str_replace('ju1ius\Pegasus\Expression\\', '', get_class($expr)),
            $this->highlightExpression($expr)
        ));
        if ($expr instanceof Composite && $parent) {
            $this->indentStack[] = $isLast ? '  ' : '│ ';
        }
    }

    /**
     * @inheritdoc
     */
    public function leaveExpression(Grammar $grammar, Expression $expr, Composite $parent = null, $index = null)
    {
        if ($expr instanceof Composite) {
            array_pop($this->indentStack);
        }
    }

    private function highlightExpression(Expression $expr)
    {
        (new ExpressionTraverser())
            ->addVisitor($highlighter = new ExpressionHighlighter())
            ->traverse($expr);

        return $highlighter->getOutput();
    }
}
