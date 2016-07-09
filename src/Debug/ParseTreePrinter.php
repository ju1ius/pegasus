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

use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Visitor\NodeVisitor;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
final class ParseTreePrinter extends NodeVisitor
{
    /**
     * @var string
     */
    private $input;

    /**
     * @var int
     */
    private $depth = 0;

    /**
     * @var Node
     */
    private $errorNode;

    /**
     * @var string
     */
    private $output;

    /**
     * @var bool
     */
    private $returnOutput;

    public function __construct(/*$input, */$errorNode = null, $returnOutput = true)
    {
        //$this->input = $input;
        $this->errorNode = $errorNode;
        $this->returnOutput = $returnOutput;
    }

    /**
     * @inheritDoc
     */
    public function beforeTraverse($node)
    {
        $this->depth = 0;
        $this->output = '';
    }

    /**
     * @inheritDoc
     */
    public function afterTraverse($node)
    {
        if ($this->returnOutput) {
            return $this->output;
        }

        echo $this->output . PHP_EOL;

        return null;
    }

    /**
     * @inheritDoc
     */
    public function enterNode($node)
    {
        $numChildren = count($node->children);
        if ($numChildren) {
            $this->depth++;
        }

        $indent = str_repeat('│ ', $this->depth - 1);
        $indent .= $numChildren ? '├ ' : '└ ';
        $this->output .= sprintf(
            '%s<%s("%s")@[%d..%d]%s>' . PHP_EOL,
            $indent,
            str_replace('ju1ius\\Pegasus\\', '', get_class($node)),
            $node->name,
            $node->start,
            $node->end,
            $node->value ? sprintf(': "%s"', $node->value) : ''
        );

        if ($this->errorNode === $node) {
            $this->output .= sprintf(
                "╠%s═▲▲▲ Error was here!\n",
                str_repeat('═', $this->depth - 1)
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function leaveNode($node)
    {
        if (count($node->children)) {
            $this->depth--;
        }
    }
}
