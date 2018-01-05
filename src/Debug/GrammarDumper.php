<?php declare(strict_types=1);
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
use ju1ius\Pegasus\Expression\ExpressionTraverser;
use ju1ius\Pegasus\Grammar\GrammarTraverser;
use ju1ius\Pegasus\Utils\Str;
use ju1ius\Pegasus\Grammar\GrammarVisitor;
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
    public static function dump(Grammar $grammar, OutputInterface $output): void
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
        $this->output->write(
            sprintf(
                '<class>Rule</class> <rule>%s</rule> <d>=</d> ',
                $expr->getName()
            )
        );
        ExpressionHighlighter::highlight($expr, $this->output);
        $this->output->writeln('');
    }

    /**
     * @inheritdoc
     */
    public function enterExpression(Expression $expr, ?int $index = null, bool $isLast = false)
    {
        $hasParent = $index !== null;
        $indent = $hasParent ? '  ' : '<d>└ </d>';
        if ($hasParent) {
            $indent .= implode('', $this->indentStack);
            $indent .= $isLast ? '└ ' : '├ ';
        }

        $this->output->write(sprintf(
            '<d>%s</d><class>%s</class> ',
            $indent,
            Str::className($expr)
        ));
        ExpressionHighlighter::highlight($expr, $this->output);
        $this->output->writeln('');

        if ($expr instanceof Composite && $hasParent) {
            $this->indentStack[] = $isLast ? '  ' : '│ ';
        }
    }

    /**
     * @inheritdoc
     */
    public function leaveExpression(Expression $expr, ?int $index = null, bool $isLast = false)
    {
        if ($expr instanceof Composite) {
            array_pop($this->indentStack);
        }
    }
}
