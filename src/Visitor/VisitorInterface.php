<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace ju1ius\Pegasus\Visitor;


/**
 * Generic Visitor interface
 *
 */
interface VisitorInterface
{
    /**
     * Called once before traversal.
     *
     */
    public function beforeTraverse($node);

    /**
     * Called when entering a node.
     *
     */
    public function enterNode($node);

    /**
     * Called when leaving a node.
     *
     */
    public function leaveNode($node);

    /**
     * Called once after traversal.
     *
     */
    public function afterTraverse($nodes);
}
