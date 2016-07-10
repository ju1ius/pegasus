<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Visitor;

use ju1ius\Pegasus\Node;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
abstract class NodeVisitor implements NodeVisitorInterface
{
    /**
     * @inheritDoc
     */
    public function beforeTraverse(Node $node)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function afterTraverse(Node $node)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function enterNode(Node $node, Node $parent = null, $index = null)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function leaveNode(Node $node, Node $parent = null, $index = null)
    {
        return null;
    }
}
