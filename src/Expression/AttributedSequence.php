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
use ju1ius\Pegasus\Grammar\Exception\MissingSemanticInAttributedSequence;
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
    public function __toString()
    {
        return implode(' ', $this->stringMembers());
    }

    /**
     * @inheritDoc
     */
    public function isCapturing()
    {
        /** @var Expression $last */
        $last = end($this->children);

        return $last->isCapturing();
    }

    /**
     * @inheritDoc
     */
    public function isCapturingDecidable()
    {
        return false;
    }

    public function match($text, $pos, ParserInterface $parser, Scope $scope)
    {
        $action = end($this->children);
        $actionIndex = key($this->children);
        if (!$action instanceof Semantic) {
            throw new MissingSemanticInAttributedSequence($this);
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
            $length = $node->end - $node->start;
            $newPos += $length;
            $totalLength += $length;
            if ($node->isTransient) {
                continue;
            }
            $children[] = $node;
        }

        return $parser->apply($action, $newPos, new Scope($scope->getBindings(), $children));
    }
}
