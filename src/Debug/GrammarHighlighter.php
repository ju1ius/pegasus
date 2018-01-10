<?php declare(strict_types=1);
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
use ju1ius\Pegasus\Expression\Decorator\Trace;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\GrammarTraverser;
use ju1ius\Pegasus\Grammar\GrammarVisitor;
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
    private $expressionHighlighter;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
        $this->expressionHighlighter = new ExpressionHighlighter($output);
    }

    /**
     * @param Grammar $grammar
     * @param OutputInterface $output
     *
     * @throws Grammar\Exception\SelfReferencingRule
     */
    public static function highlight(Grammar $grammar, OutputInterface $output): void
    {
        (new GrammarTraverser(false))
            ->addVisitor($highlighter = new self($output))
            ->traverse($grammar);
    }

    public function beforeTraverse(Grammar $grammar): ?Grammar
    {
        if ($name = $grammar->getName()) {
            $this->output->writeln(sprintf(
                '<directive>%%name</directive> <class>%s</class>',
                $name
            ));
        }
        $this->output->writeln(sprintf(
            '<directive>%%start</directive> <rule>%s</rule>',
            $grammar->getStartRule()
        ));
        $this->output->writeln('');

        return null;
    }

    public function enterRule(Grammar $grammar, Expression $expr)
    {
        if ($expr instanceof Trace) $expr = $expr[0];

        if ($grammar->isInlined($expr->getName())) {
            $this->output->write(sprintf('<directive>%%inline</directive> '));
        }
        $this->output->write(sprintf(
            '<rule>%s</rule> <d>=</d> ',
            $expr->getName()
        ));
        $this->expressionHighlighter->beforeTraverse($expr);
    }

    public function leaveRule(Grammar $grammar, Expression $expr)
    {
        $this->expressionHighlighter->afterTraverse($expr);
        $this->output->writeln(['', '']);
    }

    /**
     * @inheritDoc
     */
    public function enterExpression(Expression $expr, ?int $index = null, bool $isLast = false)
    {
        $this->expressionHighlighter->enterExpression($expr, $index, $isLast);
    }

    /**
     * @inheritDoc
     */
    public function leaveExpression(Expression $expr, ?int $index = null, bool $isLast = false)
    {
        $this->expressionHighlighter->leaveExpression($expr, $index, $isLast);
    }
}
