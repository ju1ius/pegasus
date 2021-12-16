<?php declare(strict_types=1);

namespace ju1ius\Pegasus\MetaGrammar;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\CST\Transform;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Application\Reference;
use ju1ius\Pegasus\Expression\Application\Super;
use ju1ius\Pegasus\Expression\Combinator\OneOf;
use ju1ius\Pegasus\Expression\Combinator\Sequence;
use ju1ius\Pegasus\Expression\Decorator;
use ju1ius\Pegasus\Expression\Decorator\Assert;
use ju1ius\Pegasus\Expression\Decorator\Cut;
use ju1ius\Pegasus\Expression\Decorator\Ignore;
use ju1ius\Pegasus\Expression\Decorator\Bind;
use ju1ius\Pegasus\Expression\Decorator\NodeAction;
use ju1ius\Pegasus\Expression\Decorator\Not;
use ju1ius\Pegasus\Expression\Decorator\OneOrMore;
use ju1ius\Pegasus\Expression\Decorator\Optional;
use ju1ius\Pegasus\Expression\Decorator\Quantifier;
use ju1ius\Pegasus\Expression\Decorator\Token;
use ju1ius\Pegasus\Expression\Decorator\ZeroOrMore;
use ju1ius\Pegasus\Expression\Terminal\Any;
use ju1ius\Pegasus\Expression\Terminal\BackReference;
use ju1ius\Pegasus\Expression\Terminal\CapturingRegExp;
use ju1ius\Pegasus\Expression\Terminal\EOF;
use ju1ius\Pegasus\Expression\Terminal\Epsilon;
use ju1ius\Pegasus\Expression\Terminal\Fail;
use ju1ius\Pegasus\Expression\Terminal\Literal;
use ju1ius\Pegasus\Expression\Terminal\NonCapturingRegExp;
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

    private Grammar $grammar;
    private ?string $currentRule;
    private ?string $startRule;
    private ?string $parentGrammar;
    private array $imports;
    private bool $inlining;
    private bool $lexical;

    public function getImports(): array
    {
        return $this->imports;
    }

    public function getParent(): ?string
    {
        return $this->parentGrammar;
    }

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
     * @throws Grammar\Exception\RuleNotFound
     */
    protected function afterTraverse(mixed $node): Grammar
    {
        if ($this->startRule) {
            $this->grammar->setStartRule($this->startRule);
        }

        return $this->grammar;
    }

    //
    // Directives
    // --------------------------------------------------------------------------------------------------------------

    private function leave_import_directive(Node $node, string $alias, array $path)
    {
        [$quoteChar, $string] = $path;

        $this->imports[$alias] = $string;
    }

    private function leave_grammar_directive(Node $node, string $name, ?string $parent = null)
    {
        $this->grammar->setName($name);
        if ($parent) {
            $this->parentGrammar = $parent;
        }
    }

    private function leave_start_directive(Node $node, string $name)
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

    private function leave_labeled(Node $node, $label, Expression $labelable): Bind
    {
        return new Bind($label, $labelable);
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

    private function leave_quantifier(Node $node, Node $regexp): Quantifier
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

    private function leave_cut(Node $node, ...$args): Cut
    {
        return new Cut();
    }

    private function leave_suffixed(Node $node, Expression $suffixable, Decorator $suffix): Decorator
    {
        $suffix[0] = $suffixable;

        return $suffix;
    }

    //
    // Terminal Expressions
    // --------------------------------------------------------------------------------------------------------------

    private function leave_STRING(Node $node, Node $regexp): array
    {
        [, $quoteChar, $string] = $regexp->attributes['groups'];

        return [$quoteChar, $string];
    }

    private function leave_literal(Node $node, $parts): Literal
    {
        [$quoteChar, $string] = $parts;
        if ($quoteChar === '"') {
            $string = StringEscapes::unescape($string);
        }

        return new Literal($string, '', $quoteChar);
    }

    private function leave_any(Node $node)
    {
        return new Any();
    }

    private function leave_word_literal(Node $node, string $word): Word
    {
        return new Word($word);
    }

    private function leave_regexp(Node $node, Node $regexp): RegExp
    {
        [, $pattern, $flags] = $regexp->attributes['groups'];
        // str_split returns [0 => ''] for the empty string !
        $flags = $flags ? str_split($flags) : [];

        if (PCREGroupInfo::captureCount($pattern) > 0) {
            return new CapturingRegExp($pattern, $flags);
        }

        return new NonCapturingRegExp($pattern, $flags);
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
