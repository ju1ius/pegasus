<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Expression\Decorator;

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Combinator\OneOf;
use ju1ius\Pegasus\Expression\Decorator;
use ju1ius\Pegasus\Parser\Parser;

/**
 * A sequence that must have a name, used to create "inline" rules.
 */
final class NodeAction extends Decorator
{
    /**
     * @var string
     */
    private $label;

    /**
     * @inheritDoc
     */
    public function __construct(Expression $child = null, string $label)
    {
        $this->label = $label;
        parent::__construct($child, '');
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function matches(string $text, Parser $parser): Node|bool
    {
        $start = $parser->pos;
        $capturing = $parser->isCapturing;
        if ($result = $this->children[0]->matches($text, $parser)) {
            if (!$capturing) {
                return $result;
            }
            if ($result === true) {
                return new Node\Decorator($this->label, $start, $parser->pos);
            }
            if (!$result->name) {
                $result->name = $this->label;

                return $result;
            }

            return new Node\Decorator($this->label, $start, $parser->pos, $result);
        }
        $parser->pos = $start;
        return false;
    }

    public function __toString(): string
    {
        $child = $this->children[0];
        if ($child instanceof OneOf || $child instanceof self) {
            $child = sprintf('(%s)', $child);
        } else {
            $child = (string)$child;
        }

        return sprintf('%s <= %s', $child, $this->label);
    }
}
