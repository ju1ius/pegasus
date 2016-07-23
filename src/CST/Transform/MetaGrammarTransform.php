<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\CST\Transform;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\CST\Transform;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Combinator\NamedSequence;
use ju1ius\Pegasus\Expression\Combinator\OneOf;
use ju1ius\Pegasus\Expression\Combinator\Sequence;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Expression\Decorator\Assert;
use ju1ius\Pegasus\Expression\Decorator\Label;
use ju1ius\Pegasus\Expression\Decorator\Not;
use ju1ius\Pegasus\Expression\Decorator\OneOrMore;
use ju1ius\Pegasus\Expression\Decorator\Optional;
use ju1ius\Pegasus\Expression\Decorator\Quantifier;
use ju1ius\Pegasus\Expression\Decorator\Skip;
use ju1ius\Pegasus\Expression\Decorator\Token;
use ju1ius\Pegasus\Expression\Decorator\ZeroOrMore;
use ju1ius\Pegasus\Expression\Reference;
use ju1ius\Pegasus\Expression\Super;
use ju1ius\Pegasus\Expression\Terminal\BackReference;
use ju1ius\Pegasus\Expression\Terminal\EOF;
use ju1ius\Pegasus\Expression\Terminal\Epsilon;
use ju1ius\Pegasus\Expression\Terminal\Fail;
use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\Expression\Terminal\Match;
use ju1ius\Pegasus\Expression\Terminal\RegExp;
use ju1ius\Pegasus\Expression\Terminal\Word;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\RegExp\PCREGroupInfo;

class MetaGrammarTransform extends Transform
{
    static private $QUANTIFIER_CLASSES = [
        '?' => Optional::class,
        '*' => ZeroOrMore::class,
        '+' => OneOrMore::class,
    ];

    /**
     * @var Grammar
     */
    private $grammar;

    /**
     * @var string
     */
    private $currentRule;

    /**
     * @var string
     */
    private $startRule;

    /**
     * @var string
     */
    private $parentGrammar;

    /**
     * @var bool
     */
    private $inlining;

    /**
     * @var bool
     */
    private $lexical;

    /**
     * @inheritDoc
     */
    protected function beforeTraverse(Node $node)
    {
        $this->grammar = new Grammar();
        $this->currentRule = null;
        $this->parentGrammar = null;
        $this->startRule = null;
        $this->inlining = false;
        $this->lexical = false;
    }

    /**
     * @return Grammar
     */
    protected function afterTraverse($node)
    {
        if ($this->startRule) {
            $this->grammar->setStartRule($this->startRule);
        }
        if ($this->parentGrammar) {
            // TODO: handle parent grammar !
        }

        return $this->grammar;
    }

    //
    // Directives
    // --------------------------------------------------------------------------------------------------------------

    private function leave_name_directive(Node $node, $name)
    {
        $this->grammar->setName($name);
    }

    private function leave_start_directive(Node $node, $name)
    {
        $this->startRule = $name;
    }

    private function leave_extends_directive(Node $node, $name)
    {
        $this->parentGrammar = $name;
    }

    private function leave_ci_directive(Node $node, ...$children)
    {
        $this->grammar->setCaseInsensitive(true);
    }

    private function leave_ws_directive(Node $node, Expression $expr)
    {
        return ['whitespace' => $expr];
    }

    private function leave_lexical_directive(Node $node, ...$children)
    {
        $this->lexical = true;

        return 'lexical';
    }

    private function leave_inline_directive(Node $node, ...$children)
    {
        $this->inlining = true;

        return 'inline';
    }

    //
    // Rules
    // --------------------------------------------------------------------------------------------------------------

    private function leave_rule(Node $node, $directives, $name, Expression $expr)
    {
        foreach ($directives as $directive) {
            switch ($directive) {
                case 'inline':
                    $this->grammar->inline($name);
                    break;
                case 'lexical':
                    // TODO: lexical rules
                    break;
            }
        }

        $this->grammar[$name] = $expr;

        $this->lexical = false;
        $this->inlining = false;
    }

    private function leave_RuleName(Node $node, $identifier)
    {
        $this->currentRule = $identifier;

        return $identifier;
    }

    //
    // Composite Expressions
    // --------------------------------------------------------------------------------------------------------------

    private function leave_OneOf(Node $node, Expression $alt1, $others)
    {
        if (is_array($others)) {
            $alternatives = array_merge([$alt1], $others);
        } else {
            $alternatives = [$alt1, $others];
        }

        return new OneOf($alternatives);
    }

    private function leave_Sequence(Node $node, Expression ...$children)
    {
        return new Sequence($children);
    }

    private function leave_NamedSequence(Node $node, Expression $expr, $name)
    {
        $children = $expr instanceof Composite ? iterator_to_array($expr) : [$expr];

        return new NamedSequence($children, $name);
    }

    //
    // Decorator Expressions
    // --------------------------------------------------------------------------------------------------------------

    private function leave_labeled(Node $node, $label, Expression $labelable)
    {
        return new Label($labelable, $label);
    }

    private function leave_assert(Node $node, Expression $prefixable)
    {
        return new Assert($prefixable);
    }

    private function leave_not(Node $node, Expression $prefixable)
    {
        return new Not($prefixable);
    }

    private function leave_skip(Node $node, Expression $prefixable)
    {
        return new Skip($prefixable);
    }

    private function leave_token(Node $node, Expression $prefixable)
    {
        return new Token($prefixable);
    }

    private function leave_quantifier(Node $node, $matches)
    {
        if (!empty($matches['symbol'])) {
            $class = self::$QUANTIFIER_CLASSES[$matches['symbol']];

            return new $class();
        }
        $min = (int)$matches['min'];
        if (empty($matches['not_exact'])) {
            $max = $min;
        } else {
            $max = empty($matches['max']) ? INF : (int)$matches['max'];
        }

        return new Quantifier(null, $min, $max);
    }

    private function leave_suffixed(Node $node, $suffixable, Quantifier $suffix)
    {
        $suffix[0] = $suffixable;

        return $suffix;
    }

    //
    // Terminal Expressions
    // --------------------------------------------------------------------------------------------------------------

    private function leave_literal(Node $node, $matches)
    {
        list(, $quoteChar, $literal) = $matches;

        return new Literal($literal, '', $quoteChar);
    }

    private function leave_word_literal(Node $node, $word)
    {
        return new Word($word);
    }

    private function leave_regexp(Node $node, $matches)
    {
        list(, $pattern, $flags) = $matches;
        // str_split returns [0 => ''] for the empty string !
        $flags = $flags ? str_split($flags) : [];

        if (PCREGroupInfo::captureCount($pattern) > 0) {
            return new RegExp($pattern, $flags);
        }

        return new Match($pattern, $flags);
    }

    private function leave_reference(Node $node, $identifier)
    {
        return new Reference($identifier);
    }

    private function leave_back_reference(Node $node, $identifier)
    {
        return new BackReference($identifier);
    }

    private function leave_super_call(Node $node, $identifier = null)
    {
        return new Super($identifier ?: $this->currentRule);
    }

    private function leave_eof(Node $node, ...$children)
    {
        return new EOF();
    }

    private function leave_epsilon(Node $node, ...$children)
    {
        return new Epsilon();
    }

    private function leave_fail(Node $node, ...$children)
    {
        return new Fail();
    }
}