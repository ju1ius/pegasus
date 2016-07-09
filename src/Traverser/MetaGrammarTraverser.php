<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Traverser;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Assert;
use ju1ius\Pegasus\Expression\AttributedSequence;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Expression\EOF;
use ju1ius\Pegasus\Expression\Epsilon;
use ju1ius\Pegasus\Expression\Fail;
use ju1ius\Pegasus\Expression\Label;
use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Expression\NamedSequence;
use ju1ius\Pegasus\Expression\NodeAction;
use ju1ius\Pegasus\Expression\Not;
use ju1ius\Pegasus\Expression\OneOf;
use ju1ius\Pegasus\Expression\OneOrMore;
use ju1ius\Pegasus\Expression\Optional;
use ju1ius\Pegasus\Expression\Quantifier;
use ju1ius\Pegasus\Expression\Reference;
use ju1ius\Pegasus\Expression\RegExp;
use ju1ius\Pegasus\Expression\SemanticAction;
use ju1ius\Pegasus\Expression\Sequence;
use ju1ius\Pegasus\Expression\Skip;
use ju1ius\Pegasus\Expression\Token;
use ju1ius\Pegasus\Expression\ZeroOrMore;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Utils\RegExpUtil;

class MetaGrammarTraverser extends NamedNodeTraverser
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
    private $startRule;

    /**
     * @inheritDoc
     */
    protected function beforeTraverse(Node $node)
    {
        $this->grammar = new Grammar();
        $this->startRule = null;
    }

    /**
     * @return Grammar
     */
    protected function afterTraverse($node)
    {
        if ($this->startRule) {
            $this->grammar->setStartRule($this->startRule);
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

    private function leave_ci_directive(Node $node, ...$children)
    {
        $this->grammar->setCaseInsensitive(true);
    }

    private function leave_ws_directive(Node $node, Expression $expr)
    {
        return ['whitespace' => $expr];
    }

    private function leave_tokens_directive(Node $node, Expression $expr)
    {
        return ['tokens' => $expr];
    }

    //
    // Rules
    // --------------------------------------------------------------------------------------------------------------

    private function leave_rule(Node $node, $name, Expression $expr)
    {
        $this->grammar[$name] = $expr;
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

    private function leave_AttributedSequence(Node $node, Expression ...$children)
    {
        return new AttributedSequence($children);
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
        if (!empty($matches[1])) {
            $class = self::$QUANTIFIER_CLASSES[$matches[1]];

            return new $class();
        }
        $min = (int)$matches[2];
        $max = !empty($matches[3]) ? (int)$matches[3] : INF;

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
        $quoteChar = $matches[1];
        $str = $matches[2];

        return new Literal($str, '', $quoteChar);
    }

    private function leave_regexp(Node $node, $matches)
    {
        list(, $pattern, $flags) = $matches;

        if (RegExpUtil::hasCapturingGroups($pattern)) {
            return new RegExp($pattern, str_split($flags));
        }

        return new Expression\Match($pattern, str_split($flags));
    }

    private function leave_reference(Node $node, $identifier)
    {
        return new Reference($identifier);
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

    //
    // Semantics
    // --------------------------------------------------------------------------------------------------------------

    private function leave_node_action(Node $node, $nodeClass, $nodeName)
    {
        return new NodeAction($nodeName, $nodeClass ?: '');
    }

    private function leave_semantic_action(Node $node, $identifier)
    {
        return new SemanticAction($identifier);
    }

    //
    // Expression parts
    // --------------------------------------------------------------------------------------------------------------

    private function leave_parenthesized(Node $node, ...$children)
    {
        return $children[0];
    }
}
