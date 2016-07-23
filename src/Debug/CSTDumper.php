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

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\CST\NodeTraverser;
use ju1ius\Pegasus\CST\NodeVisitor;
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
     * @param OutputInterface               $output
     * @param \ju1ius\Pegasus\CST\Node|null $errorNode
     */
    public function __construct(OutputInterface $output, Node $errorNode = null)
    {
        $this->output = $output;
        $this->errorNode = $errorNode;
    }

    /**
     * @param Node            $node
     * @param OutputInterface $output
     * @param Node|null       $errorNode
     */
    public static function dump(Node $node, OutputInterface $output, Node $errorNode = null)
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
    public function enterNode(Node $node, $index = null, $isLast = false)
    {
        $hasParent = $index !== null;
        if ($hasParent) {
            $indent = implode('', $this->indentStack);
            $indent .= $isLast ? '└ ' : '├ ';
            $this->output->write(sprintf('<d>%s</d>', $indent));
        }
        $this->output->write(sprintf(
            '<class>%s</class>',
            str_replace('ju1ius\\Pegasus\\CST\\Node\\', '', get_class($node))
        ));
        if ($node->name) {
            $this->output->write(sprintf('<d>("</d>%s<d>")</d>', $node->name));
        }
        $this->output->write(sprintf(
            '<sym>@</sym><d>[</d>%d<d>..</d>%d<d>]</d>',
            $node->start,
            $node->end
        ));
        if ($node->value) {
            $this->output->write(sprintf('<d>: "</d><term>%s</term><d>"</d>', $node->value));
        }
        $this->output->writeln('');

        if ($this->errorNode === $node) {
            $this->output->writeln(sprintf(
                "<error>%s╌╌┘ Error was here. </error>",
                str_repeat('╌╌', count($this->indentStack))
            ));
        }
        if (count($node) && $hasParent) {
            $this->indentStack[] = $isLast ? '  ' : '│ ';
        }
    }

    /**
     * @inheritDoc
     */
    public function leaveNode(Node $node, $index = null, $isLast = false)
    {
        if (count($node)) {
            array_pop($this->indentStack);
        }
    }
}