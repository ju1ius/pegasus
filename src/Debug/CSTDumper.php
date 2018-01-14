<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Debug;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\CST\NodeTraverser;
use ju1ius\Pegasus\CST\NodeVisitor;
use ju1ius\Pegasus\Utils\Str;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
final class CSTDumper extends NodeVisitor
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var Node
     */
    private $errorNode;

    /**
     * @var array
     */
    private $indentStack;

    /**
     * CSTDumper constructor.
     *
     * @param OutputInterface   $output
     * @param Node|null         $errorNode
     */
    public function __construct(OutputInterface $output, ?Node $errorNode = null)
    {
        $this->output = $output;
        $this->errorNode = $errorNode;
    }

    /**
     * @param Node            $node
     * @param OutputInterface $output
     * @param Node|null       $errorNode
     */
    public static function dump(Node $node, OutputInterface $output, ?Node $errorNode = null)
    {
        (new NodeTraverser())
            ->addVisitor(new self($output, $errorNode))
            ->traverse($node);
    }

    /**
     * @inheritDoc
     */
    public function beforeTraverse(Node $node)
    {
        $this->indentStack = [];
    }

    /**
     * @inheritDoc
     */
    public function enterNode(Node $node, ?int $index = null, bool $isLast = false)
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
            $this->output->write(sprintf('<d>("</d>%s<d>")</d>', $node->name));
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
            $this->output->write(sprintf('<d>: "</d><term>%s</term><d>"</d>', $node->value));
        }
        $this->output->writeln('');

        if ($this->errorNode === $node) {
            $this->output->writeln(sprintf(
                "<error>%s╌╌┘ Error was here. </error>",
                str_repeat('╌╌', count($this->indentStack))
            ));
        }
        if ($node instanceof Node\Composite && $node->children && $hasParent) {
            $this->indentStack[] = $isLast ? '  ' : '│ ';
        }
    }

    /**
     * @inheritDoc
     */
    public function leaveNode(Node $node, ?int $index = null, bool $isLast = false)
    {
        if ($node instanceof Node\Composite && $node->children) {
            array_pop($this->indentStack);
        }
    }
}
