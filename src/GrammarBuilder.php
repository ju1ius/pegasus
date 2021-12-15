<?php declare(strict_types=1);

namespace ju1ius\Pegasus;

/**
 * This class provides a fluent interface for building grammars.
 */
class GrammarBuilder extends ExpressionBuilder
{
    /**
     * Current rule name.
     */
    protected string $currentRule;

    protected function __construct(
        protected Grammar $grammar
    ) {
        parent::__construct();
    }

    public static function create(string $name = ''): static
    {
        $grammar = new Grammar();
        if ($name) {
            $grammar->setName($name);
        }

        return new self($grammar);
    }

    public static function of(Grammar $grammar): static
    {
        return new self($grammar);
    }

    /**
     * Ends the current rule and adds it to the grammar.
     * @return $this
     */
    public function endRule(): static
    {
        $this->endAll();
        if ($this->rootExpr && $this->compositeStack->isEmpty()) {
            $this->grammar[$this->currentRule] = $this->rootExpr;
            $this->rootExpr = null;
        }

        return $this;
    }

    public function getGrammar(): Grammar
    {
        $this->endRule();

        return $this->grammar;
    }

    /**
     * @return $this
     */
    public function rule(string $name): static
    {
        $this->endRule();
        $this->currentRule = $name;

        return $this;
    }

    /**
     * @param string $identifier Defaults to the current rule.
     * @return $this
     */
    public function super(string $identifier = ''): static
    {
        parent::super($identifier ?: $this->currentRule);

        return $this;
    }
}
