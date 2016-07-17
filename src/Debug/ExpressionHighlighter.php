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
use ju1ius\Pegasus\Expression\Assert;
use ju1ius\Pegasus\Expression\Combinator;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Expression\Decorator;
use ju1ius\Pegasus\Expression\GroupMatch;
use ju1ius\Pegasus\Expression\Label;
use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Expression\Match;
use ju1ius\Pegasus\Expression\NamedSequence;
use ju1ius\Pegasus\Expression\Not;
use ju1ius\Pegasus\Expression\OneOf;
use ju1ius\Pegasus\Expression\Quantifier;
use ju1ius\Pegasus\Expression\Reference;
use ju1ius\Pegasus\Expression\RegExp;
use ju1ius\Pegasus\Expression\Sequence;
use ju1ius\Pegasus\Expression\Skip;
use ju1ius\Pegasus\Expression\Super;
use ju1ius\Pegasus\Expression\Terminal;
use ju1ius\Pegasus\Expression\Token;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Traverser\ExpressionTraverser;
use ju1ius\Pegasus\Visitor\ExpressionVisitor;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class ExpressionHighlighter extends ExpressionVisitor
{
    /**
     * @var string
     */
    private $output;

    /**
     * @var \SplStack
     */
    private $combinatorStack;

    private $ruleName = '';

    /**
     * ExpressionHighlighter constructor.
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
     *
     * @return string
     */
    public static function highlight(Expression $expr, OutputInterface $output)
    {
        (new ExpressionTraverser())
            ->addVisitor($highlighter = new self($output))
            ->traverse($expr);
    }

    /**
     * @inheritdoc
     */
    public function beforeTraverse(Expression $expr)
    {
        $this->combinatorStack = new \SplStack();
        $this->ruleName = $expr->name;
    }

    /**
     * @inheritdoc
     */
    public function enterExpression(Expression $expr, $index = null, $isLast = false)
    {
        if ($index && !$this->combinatorStack->isEmpty()) {
            $top = $this->combinatorStack->top();
            if ($top instanceof OneOf) {
                $this->output->write(' <d>|</d> ');
            } else {
                $this->output->write(' ');
            }
        }
        if ($expr instanceof Reference) {
            $this->output->write(sprintf('<ref>%s</ref>', $expr->identifier));
        } elseif ($expr instanceof Super) {
            $this->output->write('<class>super</class>');
            if ($expr->identifier !== $this->ruleName) {
                $this->output->write('<d>::</d>');
                $this->output->write(sprintf('<ref>%s</ref>', $expr->identifier));
            }
        } elseif ($expr instanceof Terminal) {
            if ($expr instanceof Literal) {
                $this->output->write(sprintf(
                    '<d>%1$s</d><term>%2$s</term><d>%1$s</d>',
                    $expr->quoteCharacter,
                    $expr->literal
                ));
            } elseif ($expr instanceof Match || $expr instanceof RegExp) {
                $this->output->write(sprintf(
                    '<d>/</d><term>%s</term><d>/</d><term>%s</term>',
                    $expr->pattern,
                    implode('', $expr->flags)
                ));
            } elseif ($expr instanceof GroupMatch) {
                $this->output->write('<class>GroupMatch</class>');
                $this->output->write(sprintf(
                    '[<d>/</d><term>%s</term><d>/</d><term>%s</term><d>, </d><term>%s</term>]',
                    $expr->getPattern(),
                    implode('', $expr->getFlags()),
                    $expr->getCaptureCount()
                ));
            } else {
                $this->output->write(sprintf('<kw>%s</kw>', $expr));
            }
        } elseif ($expr instanceof Decorator) {
            switch (get_class($expr)) {
                case Assert::class:
                    $this->output->write('<sym>&</sym>');
                    break;
                case Not::class:
                    $this->output->write('<sym>!</sym>');
                    break;
                case Skip::class:
                    $this->output->write('<sym>~</sym>');
                    break;
                case Label::class:
                    $this->output->write(sprintf('<label>%s</label><d>:</d>', $expr->label));
                    break;
                case Token::class:
                    $this->output->write('<sym>@</sym>');
                    break;
            }
            if ($this->needsParenthesesAroundDecorator($expr)) {
                $this->output->write('<d>(</d>');
            }
        } elseif ($expr instanceof Combinator) {
            if ($this->needsParenthesesAroundCombinator($expr)) {
                $this->output->write('<d>(</d>');
            }
            $this->combinatorStack->push($expr);
        }
    }

    /**
     * @inheritdoc
     */
    public function leaveExpression(Expression $expr, $index = null, $isLast = false)
    {
        if ($expr instanceof Decorator) {
            if ($this->needsParenthesesAroundDecorator($expr)) {
                $this->output->write('<d>)</d>');
            }
            if ($expr instanceof Quantifier) {
                if ($expr->isOneOrMore()) {
                    $symbol = '+';
                } elseif ($expr->isZeroOrMore()) {
                    $symbol = '*';
                } elseif ($expr->isOptional()) {
                    $symbol = '?';
                } else {
                    $symbol = sprintf(
                        '{%s,%s}',
                        $expr->min,
                        $expr->max === INF ? '' : $expr->max
                    );
                }
                $this->output->write(sprintf('<q>%s</q>', $symbol));
            }
        } elseif ($expr instanceof Combinator) {
            $this->combinatorStack->pop();
            if ($expr instanceof NamedSequence) {
                $this->output->write(sprintf(' <d><=</d> <id>%s</id>', $expr->label));
            }
            if ($this->needsParenthesesAroundCombinator($expr)) {
                $this->output->write('<d>)</d>');
            }
        }
    }

    /**
     * @param Combinator $expr
     *
     * @return bool
     */
    private function needsParenthesesAroundCombinator(Combinator $expr)
    {
        if ($expr instanceof OneOf) {
            return !$this->combinatorStack->isEmpty();
        }
        if ($this->combinatorStack->isEmpty()) {
            return false;
        }
        $top = $this->combinatorStack->top();

        return $top instanceof Sequence || $top instanceof NamedSequence;
    }

    /**
     * @param Decorator $expr
     *
     * @return bool
     */
    private function needsParenthesesAroundDecorator(Decorator $expr)
    {
        return $expr[0] instanceof Composite;
    }
}
