<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Expression;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Parser\ParserInterface;
use ju1ius\Pegasus\Parser\Scope;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class NodeAction extends Semantic
{
    /**
     * @const string
     */
    const DEFAULT_NODE_CLASS = Node::class;

    /**
     * @var string
     */
    private $nodeName;

    /**
     * @var string
     */
    private $nodeClass;

    /**
     * NodeAction constructor.
     *
     * @param string $nodeName
     * @param string $nodeClass
     */
    public function __construct($nodeName = '', $nodeClass = '')
    {
        $this->nodeName = $nodeName;
        $this->nodeClass = $nodeClass ?: self::DEFAULT_NODE_CLASS;
        parent::__construct();
    }

    /**
     * @return string
     */
    public function getNodeName()
    {
        return $this->nodeName;
    }

    /**
     * @return string
     */
    public function getNodeClass()
    {
        return $this->nodeClass;
    }

    public function match($text, $pos, ParserInterface $parser, Scope $scope)
    {
        $captures = $scope->getCaptures();
        $first = reset($captures);
        // TODO: in an attributed sequence $pos is the end of the last node
        $last = end($captures);

        return new $this->nodeClass($this, $text, $first->start, $last->end, $captures);
    }

    public function __toString()
    {
        return sprintf(
            '<%s: "%s">',
            $this->nodeClass,
            $this->nodeName
        );
    }
}
