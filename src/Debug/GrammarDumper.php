<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Debug;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\GrammarTraverser;
use ju1ius\Pegasus\Grammar\GrammarVisitor;
use ju1ius\Pegasus\Utils\Str;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Visitor that dumps a grammar's rules & expression tree.
 */
final class GrammarDumper extends GrammarVisitor
{
    private array $indentStack;

    public function __construct(
        private OutputInterface $output,
    ) {
    }

    /**
     * @throws Grammar\Exception\SelfReferencingRule
     */
    public static function dump(Grammar $grammar, OutputInterface $output): void
    {
        (new GrammarTraverser(false))
            ->addVisitor(new self($output))
            ->traverse($grammar);
    }

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

    public function leaveExpression(Expression $expr, ?int $index = null, bool $isLast = false)
    {
        if ($expr instanceof Composite) {
            array_pop($this->indentStack);
        }
    }
}
