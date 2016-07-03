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

use ju1ius\Pegasus\Node;
use ju1ius\Pegasus\Parser\ParserInterface;
use ju1ius\Pegasus\Parser\Scope;

/**
 * Combines one or more expression with a semantic action,
 * matches the expressions in sequence and applies the action to the captured results.
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
class AttributedSequence extends Composite
{
    public function match($text, $pos, ParserInterface $parser, Scope $scope)
    {
        $action = end($this->children);
        $actionIndex = key($this->children);
        if (!$action instanceof Semantic) {
            throw new \LogicException('Last expression of `%s` should be instance of Semantic.');
        }

        $newPos = $pos;
        $totalLength = 0;
        $children = [];
        foreach ($this->children as $i => $child) {
            if ($i === $actionIndex) {
                break;
            }
            $node = $parser->apply($child, $newPos, $scope);
            if (!$node) {
                return null;
            }
            $children[] = $node;
            $length = $node->end - $node->start;
            $newPos += $length;
            $totalLength += $length;
        }

        return $parser->apply($action, $newPos, new Scope($scope->getBindings(), $children));
    }

    public function asRightHandSide()
    {
        return implode(' ', $this->stringMembers());
    }

    public function isCapturing()
    {
        $last = end($this->children);

        return $last->isCapturing;
    }

    public function isCapturingDecidable()
    {
        return false;
    }
}
