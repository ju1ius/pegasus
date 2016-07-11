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

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Traverser\ExpressionTraverser;
use ju1ius\Pegasus\Visitor\ExpressionVisitor;
use Symfony\Component\Console\Output\OutputInterface;

final class ExpressionDumper extends ExpressionVisitor
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var array
     */
    private $indentStack;

    /**
     * ExpressionDumper constructor.
     *
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @param Expression      $expr
     * @param OutputInterface $output
     */
    public static function dump(Expression $expr, OutputInterface $output)
    {
        (new ExpressionTraverser())
            ->addVisitor(new self($output))
            ->traverse($expr);
    }

    /**
     * @inheritdoc
     */
    public function beforeTraverse(Expression $expr)
    {
        $this->indentStack = [];
    }

    /**
     * @inheritdoc
     */
    public function enterExpression(Expression $expr, Composite $parent = null, $index = null)
    {
        $isLast = !$parent || $index === count($parent) - 1;
        $indent = '';
        if ($parent) {
            $indent .= implode('', $this->indentStack);
            $indent .= $isLast ? '└ ' : '├ ';
        }

        $this->output->write(sprintf(
            '<d>%s</d><class>%s</class><d>:</d> ',
            $indent,
            str_replace('ju1ius\Pegasus\Expression\\', '', get_class($expr))
        ));
        ExpressionHighlighter::highlight($expr, $this->output);
        $this->output->writeln('');

        if ($expr instanceof Composite && $parent) {
            $this->indentStack[] = $isLast ? '  ' : '│ ';
        }
    }

    /**
     * @inheritdoc
     */
    public function leaveExpression(Expression $expr, Composite $parent = null, $index = null)
    {
        if ($expr instanceof Composite) {
            array_pop($this->indentStack);
        }
    }
}
