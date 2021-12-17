<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Debug;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Decorator\Trace;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\GrammarTraverser;
use ju1ius\Pegasus\Grammar\GrammarVisitor;
use Symfony\Component\Console\Output\OutputInterface;

final class GrammarHighlighter extends GrammarVisitor
{
    private ExpressionHighlighter $expressionHighlighter;

    public function __construct(
        private OutputInterface $output,
    ) {
        $this->expressionHighlighter = new ExpressionHighlighter($output);
    }

    /**
     * @throws Grammar\Exception\SelfReferencingRule
     */
    public static function highlight(Grammar $grammar, OutputInterface $output): void
    {
        (new GrammarTraverser(false))
            ->addVisitor(new self($output))
            ->traverse($grammar);
    }

    public function beforeTraverse(Grammar $grammar): ?Grammar
    {
        if ($name = $grammar->getName()) {
            $this->output->writeln(sprintf(
                '<directive>@name</directive> <class>%s</class>',
                $name
            ));
        }
        $this->output->writeln(sprintf(
            '<directive>@start</directive> <rule>%s</rule>',
            $grammar->getStartRule()
        ));
        $this->output->writeln('');

        return null;
    }

    public function enterRule(Grammar $grammar, Expression $expr)
    {
        if ($expr instanceof Trace) $expr = $expr[0];

        if ($grammar->isInlined($expr->getName())) {
            $this->output->write('<directive>@inline</directive> ');
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

    public function enterExpression(Expression $expr, ?int $index = null, bool $isLast = false)
    {
        $this->expressionHighlighter->enterExpression($expr, $index, $isLast);
    }

    public function leaveExpression(Expression $expr, ?int $index = null, bool $isLast = false)
    {
        $this->expressionHighlighter->leaveExpression($expr, $index, $isLast);
    }
}
