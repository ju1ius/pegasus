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

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
abstract class NodeVisitor implements NodeVisitorInterface
{
    /**
     * @inheritDoc
     */
    public function beforeTraverse($node)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function afterTraverse($node)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function enterNode($node)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function leaveNode($node)
    {
        return null;
    }
}
