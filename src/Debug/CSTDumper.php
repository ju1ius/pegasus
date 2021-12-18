<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Debug;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\CST\NodeTraverser;
use ju1ius\Pegasus\CST\NodeVisitor;
use ju1ius\Pegasus\Utils\Str;
use Symfony\Component\Console\Output\OutputInterface;

final class CSTDumper extends NodeVisitor
{
    private OutputInterface $output;

    private ?Node $errorNode;

    private array $indentStack;

    public function __construct(OutputInterface $output, ?Node $errorNode = null)
    {
        $this->output = $output;
        $this->errorNode = $errorNode;
    }

    public static function dump(Node $node, OutputInterface $output, ?Node $errorNode = null)
    {
        (new NodeTraverser())
            ->addVisitor(new self($output, $errorNode))
            ->traverse($node);
    }

    public function beforeTraverse(Node $node): ?Node
    {
        $this->indentStack = [];
        return null;
    }

    public function enterNode(Node $node, ?int $index = null, bool $isLast = false): ?Node
    {
        $hasParent = $index !== null;
        if ($hasParent) {
            $indent = implode('', $this->indentStack);
            $indent .= $isLast ? '└ ' : '├ ';
            $this->output->write(sprintf('<d>%s</d>', $indent));
        }
        $this->output->write(sprintf(
            '<class>%s</class>',
            Str::className($node)
        ));
        if ($node->name) {
            $this->output->write(sprintf('<d>(</d><rule>%s</rule><d>)</d>', $node->name));
        }
        $this->output->write(sprintf(
            '<sym>@</sym><d>[</d>%d<d>..</d>%d<d>]</d>',
            $node->start,
            $node->end
        ));
        if ($node instanceof Node\Invalid) {
            $this->output->write(sprintf(
                '<error> Node expected, got: `%s` </error>',
                var_export($node->value, true)
            ));
        } elseif ($node instanceof Node\Terminal) {
            $this->output->write(sprintf(
                '<d>: "</d><term>%s</term><d>"</d>',
                $this->highlightStringValue($node->value),
            ));
        }
        $this->output->writeln('');

        if ($this->errorNode === $node) {
            $this->output->writeln(sprintf(
                "<error>%s╌╌┘ Error was here. </error>",
                str_repeat('╌╌', \count($this->indentStack))
            ));
        }
        if ($node instanceof Node\Composite && $node->children && $hasParent) {
            $this->indentStack[] = $isLast ? '  ' : '│ ';
        }

        return null;
    }

    public function leaveNode(Node $node, ?int $index = null, bool $isLast = false): ?Node
    {
        if ($node instanceof Node\Composite && $node->children) {
            array_pop($this->indentStack);
        }
        return null;
    }

    private function highlightStringValue(string $value): string
    {
        return strtr($value, [
            "\n" => '<esc>\n</esc>',
            "\r" => '<esc>\r</esc>',
            "\t" => '<esc>\t</esc>',
            "\f" => '<esc>\f</esc>',
        ]);
    }
}
