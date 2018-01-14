<?php declare(strict_types=1);


namespace ju1ius\Pegasus\Trace;


use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Decorator\Trace;
use ju1ius\Pegasus\Grammar\GrammarVisitor;


final class GrammarTracer extends GrammarVisitor
{
    /**
     * @var bool
     */
    private $enable;

    public function __construct(bool $enable = true)
    {
        $this->enable = $enable;
    }

    public function enterExpression(Expression $expr, ?int $index = null, bool $isLast = false)
    {
        if ($expr instanceof Trace) {
            // Always remove the existing Trace to prevent double-wrapping
            return $expr[0];
        }
    }

    public function leaveExpression(Expression $expr, ?int $index = null, bool $isLast = false)
    {
        if ($this->enable) {
            return new Trace($expr);
        }
    }
}
