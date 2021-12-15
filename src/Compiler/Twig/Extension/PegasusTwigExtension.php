<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Compiler\Twig\Extension;

use ju1ius\Pegasus\Compiler\CompilationContext;
use ju1ius\Pegasus\Compiler\CompilerInterface;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Decorator\Quantifier;
use ju1ius\Pegasus\Expression\Terminal\CapturingRegExp;
use ju1ius\Pegasus\Utils\Str;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class PegasusTwigExtension extends AbstractExtension
{
    public function __construct(
        private CompilerInterface $compiler
    ) {
    }

    public function getName(): string
    {
        return 'pegasus';
    }

    public function getTokenParsers(): array
    {
        return [];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('render_expr', [$this, 'renderExpression'], [
                'needs_environment' => true,
                'needs_context' => true,
            ]),
            new TwigFunction('render_rule', [$this, 'renderRule'], [
                'needs_environment' => true,
                'needs_context' => true,
            ]),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('indent', [$this, 'indent']),
        ];
    }

    public function indent(
        string $text,
        int $level = 1,
        int $size = 4,
        ?callable $predicate = null
    ): string {
        $prefix = str_repeat(' ', $size * $level);
        $predicate = $predicate ?: 'trim';
        $out = '';
        // split lines while keeping linebreak chars
        $lines = preg_split('/(?<=\n)/S', $text);
        foreach ($lines as $i => $line) {
            if ($i === 0) {
                // skip first line to preserve existing space before twig opening tag
                $out .= $line;
                continue;
            }
            $out .= $predicate($line) ? $prefix . $line : $line;
        }

        return $out;
    }

    public function renderRule(
        Environment $env,
        $twigContext,
        string $name,
        Expression $expr,
        CompilationContext $context
    ): string {
        $args = array_merge($twigContext, [
            'expr' => $expr,
            'context' => $context->ofRule($name),
        ]);

        return $env->render('rule.twig', $args);
    }

    public function renderExpression(
        Environment $env,
        $twigContext,
        Expression $expr,
        CompilationContext $context,
        array $args = []
    ): string {
        $args = array_merge($twigContext, $args, [
            'expr' => $expr,
            'context' => $context,
        ]);

        $template = $this->getTemplateForExpression($expr);

        return $env->render($template, $args);
    }

    public function getTemplateForExpression(Expression $expr): string
    {
        if ($expr instanceof Quantifier) {
            if ($expr->isOptional()) {
                return 'expression/Optional.twig';
            }
            return 'expression/Quantifier.twig';
        }
        if ($expr instanceof CapturingRegExp) {
            return 'expression/Match.twig';
        }

        return sprintf('expression/%s.twig', Str::className($expr));
    }
}
