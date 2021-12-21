<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Debug;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Expression\ExpressionTraverser;
use ju1ius\Pegasus\Expression\ExpressionVisitor;
use ju1ius\Pegasus\Utils\Str;
use Symfony\Component\Console\Output\OutputInterface;

final class ExpressionDumper extends ExpressionVisitor
{
    private array $indentStack;

    public function __construct(
        private OutputInterface $output,
    ) {
    }

    public static function dump(Expression $expr, OutputInterface $output)
    {
        (new ExpressionTraverser())
            ->addVisitor(new self($output))
            ->traverse($expr);
    }

    public function beforeTraverse(Expression $expr): ?Expression
    {
        $this->indentStack = [];

        return null;
    }

    public function enterExpression(Expression $expr, ?int $index = null, bool $isLast = false): ?Expression
    {
        $indent = '';
        $hasParent = $index !== null;
        if ($hasParent) {
            $indent .= implode('', $this->indentStack);
            $indent .= $isLast ? '└ ' : '├ ';
        }

        $this->output->write(sprintf(
            '<d>%s</d><class>%s</class><d>:</d> ',
            $indent,
            Str::className($expr)
        ));
        ExpressionHighlighter::highlight($expr, $this->output);
        $this->output->writeln('');

        if ($expr instanceof Composite && $hasParent) {
            $this->indentStack[] = $isLast ? '  ' : '│ ';
        }

        return null;
    }

    public function leaveExpression(Expression $expr, ?int $index = null, bool $isLast = false): ?Expression
    {
        if ($expr instanceof Composite) {
            array_pop($this->indentStack);
        }

        return null;
    }
}
