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
     * @var int
     */
    private $depth = 0;

    /**
     * @var Node
     */
    private $errorNode;

    public function __construct($errorNode = null)
    {
        $this->errorNode = $errorNode;
    }

    /**
     * @inheritDoc
     */
    public function beforeTraverse($node)
    {
        $this->depth = 0;
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
        echo sprintf(
            '%s<%s("%s")@[%d..%d]: "%s">' . PHP_EOL,
            $indent,
            str_replace('ju1ius\Pegasus\Node', '', get_class($node)),
            $node->name,
            $node->start,
            $node->end,
            $node->getText()
        );

        if ($this->errorNode === $node) {
            echo sprintf(
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
