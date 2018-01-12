<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\MetaGrammar;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\CST\Transform;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Decorator\NodeAction;
use ju1ius\Pegasus\Expression\Combinator\OneOf;
use ju1ius\Pegasus\Expression\Combinator\Sequence;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Expression\Decorator\Assert;
use ju1ius\Pegasus\Expression\Decorator\Label;
use ju1ius\Pegasus\Expression\Decorator\Not;
use ju1ius\Pegasus\Expression\Decorator\OneOrMore;
use ju1ius\Pegasus\Expression\Decorator\Optional;
use ju1ius\Pegasus\Expression\Decorator\Quantifier;
use ju1ius\Pegasus\Expression\Decorator\Ignore;
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
use ju1ius\Pegasus\Expression\Terminal\PCREPattern;
use ju1ius\Pegasus\Expression\Terminal\RegExp;
use ju1ius\Pegasus\Expression\Terminal\Word;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\RegExp\PCREGroupInfo;

class MetaGrammarTransform extends Transform
{
    private const QUANTIFIER_CLASSES = [
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
     * @var array
     */
    private $imports;

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
        $this->imports = [];
        $this->startRule = null;
        $this->inlining = false;
        $this->lexical = false;
    }

    /**
     * @param $node
     * @return Grammar
     * @throws Grammar\Exception\RuleNotFound
     */
    protected function afterTraverse($node)
    {
        if ($this->startRule) {
            $this->grammar->setStartRule($this->startRule);
        }
        if ($this->parentGrammar) {
            // TODO: handle parent grammar !
        }
        if ($this->imports) {
            // TODO: handle imports !
        }

        return $this->grammar;
    }

    //
    // Directives
    // --------------------------------------------------------------------------------------------------------------

    private function leave_name_directive(Node $node, string $name)
    {
        $this->grammar->setName($name);
    }

    private function leave_start_directive(Node $node, string $name)
    {
        $this->startRule = $name;
    }

    private function leave_extends_directive(Node $node, string $name)
    {
        $this->parentGrammar = $name;
    }

    private function leave_import_directive(Node $node, string $alias, string $path)
    {
        $this->imports[$alias] = $path;
    }

    private function leave_ci_directive(Node $node, ...$children)
    {
        $this->grammar->setCaseInsensitive(true);
    }

    private function leave_ws_directive(Node $node, Expression $expr)
    {
        return ['whitespace' => $expr];
    }

    private function leave_lexical_directive(Node $node, ...$children): string
    {
        $this->lexical = true;

        return 'lexical';
    }

    private function leave_InlineDirective(Node $node, ...$children): string
    {
        $this->inlining = true;

        return 'inline';
    }

    //
    // Rules
    // --------------------------------------------------------------------------------------------------------------

    private function leave_rule(Node $node, $directives, string $name, Expression $expr)
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

    private function leave_RuleName(Node $node, string $identifier): string
    {
        $this->currentRule = $identifier;

        return $identifier;
    }

    //
    // Composite Expressions
    // --------------------------------------------------------------------------------------------------------------

    private function leave_OneOf(Node $node,  ...$alternatives): OneOf
    {
        return new OneOf($alternatives);
    }

    private function leave_Sequence(Node $node, Expression ...$children): Sequence
    {
        return new Sequence($children);
    }

    private function leave_NodeAction(Node $node, Expression $expr, $name): NodeAction
    {
        return new NodeAction($expr, $name);
    }

    //
    // Decorator Expressions
    // --------------------------------------------------------------------------------------------------------------

    private function leave_labeled(Node $node, $label, Expression $labelable): Label
    {
        return new Label($labelable, $label);
    }

    private function leave_assert(Node $node, Expression $prefixable): Assert
    {
        return new Assert($prefixable);
    }

    private function leave_not(Node $node, Expression $prefixable): Not
    {
        return new Not($prefixable);
    }

    private function leave_ignore(Node $node, Expression $prefixable): Ignore
    {
        return new Ignore($prefixable);
    }

    private function leave_token(Node $node, Expression $prefixable): Token
    {
        return new Token($prefixable);
    }

    private function leave_quantifier(Node $node, $regexp): Quantifier
    {
        $groups = $regexp->attributes['groups'];
        if (!empty($groups['symbol'])) {
            $class = self::QUANTIFIER_CLASSES[$groups['symbol']];

            return new $class();
        }
        $min = (int)$groups['min'];
        if (empty($groups['not_exact'])) {
            $max = $min;
        } else {
            $max = empty($groups['max']) ? null : (int)$groups['max'];
        }

        return new Quantifier(null, $min, $max);
    }

    private function leave_suffixed(Node $node, $suffixable, Quantifier $suffix): Quantifier
    {
        $suffix[0] = $suffixable;

        return $suffix;
    }

    //
    // Terminal Expressions
    // --------------------------------------------------------------------------------------------------------------

    private function leave_literal(Node $node, Node $regexp): Literal
    {
        list(, $quoteChar, $literal) = $regexp->attributes['groups'];

        return new Literal($literal, '', $quoteChar);
    }

    private function leave_word_literal(Node $node, string $word): Word
    {
        return new Word($word);
    }

    private function leave_regexp(Node $node, Node $regexp): PCREPattern
    {
        list(, $pattern, $flags) = $regexp->attributes['groups'];
        // str_split returns [0 => ''] for the empty string !
        $flags = $flags ? str_split($flags) : [];

        if (PCREGroupInfo::captureCount($pattern) > 0) {
            return new RegExp($pattern, $flags);
        }

        return new Match($pattern, $flags);
    }

    private function leave_reference(Node $node, string $identifier): Reference
    {
        return new Reference($identifier);
    }

    private function leave_back_reference(Node $node, string $identifier): BackReference
    {
        return new BackReference($identifier);
    }

    private function leave_super_call(Node $node, ?string $identifier = null): Super
    {
        return new Super($identifier ?: $this->currentRule);
    }

    private function leave_eof(Node $node, ...$children): EOF
    {
        return new EOF();
    }

    private function leave_epsilon(Node $node, ...$children): Epsilon
    {
        return new Epsilon();
    }

    private function leave_fail(Node $node, ...$children): Fail
    {
        return new Fail();
    }
}