<?php declare(strict_types=1);

namespace ju1ius\Pegasus\CST;

use Closure;
use ju1ius\Pegasus\CST\Exception\TransformException;
use ju1ius\Pegasus\CST\Node\Terminal;

/**
 * Performs a depth-first traversal of a parse tree.
 */
class Transform
{
    /**
     * @var Closure[]
     */
    private array $enterVisitors;

    /**
     * @var Closure[]
     */
    private ?array $leaveVisitors = null;

    private ?Node $rootNode = null;

    /**
     * @var Transform[]
     */
    private array $traits;

    final public function transform(Node $node)
    {
        $this->rootNode = $node;

        if ($this->leaveVisitors === null) {
            $this->buildVisitors();
        }

        $this->beforeTraverse($node);
        $node = $this->visit($node);
        $result = $this->afterTraverse($node);

        $this->rootNode = null;

        return $result;
    }

    public function addTrait(string $namespace, Transform $trait)
    {
        $this->traits[$namespace] = $trait;
    }

    protected function beforeTraverse(Node $node)
    {
    }

    /**
     * @param mixed $node
     *
     * @return mixed
     */
    protected function afterTraverse($node)
    {
        return $node;
    }

    /**
     * @param Terminal $node The node we're visiting
     *
     * @return string|string[]|Terminal
     */
    protected function leaveTerminal(Terminal $node)
    {
        if (isset($node->attributes['captures'])) {
            // used by GroupMatch
            return $node->attributes['captures'];
        }
        if (isset($node->attributes['groups'])) {
            // used by RegExp
            return $node;
        }

        return $node->value;
    }

    /**
     * @param Node  $node     The node we're visiting
     * @param array $children The results of visiting the children of that node
     */
    protected function leaveNonTerminal(Node $node, array $children): mixed
    {
        if ($node instanceof Node\Quantifier) {
            if ($node->isOptional) {
                return $children ? $children[0] : null;
            }

            return $children;
        }
        if ($node instanceof Node\Decorator) {
            return $children[0];
        }

        return \count($children) === 1 ? $children[0] : $children;
    }

    private function visit(Node $node): mixed
    {
        try {

            if ($node instanceof Node\ExternalReference) {
                return $this->visitExternalReference($node);
            }

            $name = $node->name;

            if ($name && isset($this->enterVisitors[$name])) {
                $this->enterVisitors[$name]($node);
            }

            if ($node instanceof Node\Composite) {
                $children = [];
                foreach ($node->children as $child) {
                    $children[] = $this->visit($child);
                }

                if ($name && isset($this->leaveVisitors[$name])) {
                    return $this->leaveVisitors[$name]($node, ...$children);
                }

                return $this->leaveNonTerminal($node, $children);
            }

            assert($node instanceof Terminal);
            $value = $this->leaveTerminal($node);

            if ($name && isset($this->leaveVisitors[$name])) {
                $args = \is_array($value) ? $value : [$value];

                return $this->leaveVisitors[$name]($node, ...$args);
            }

            return $value;

        } catch (TransformException $err) {
            throw $err;
        } catch (\Exception $err) {
            throw new TransformException($node, $this->rootNode, '', $err);
        }
    }

    private function visitExternalReference(Node\ExternalReference $node)
    {
        $namespace = $node->namespace;
        $name = $node->name;
        if (!isset($this->traits[$namespace])) {
            throw new \LogicException("Undefined trait: {$namespace}");
        }
        if ($name && isset($this->enterVisitors[$name])) {
            $this->enterVisitors[$name]($node);
        }

        $result = $this->traits[$namespace]->transform($node->child);

        if ($name && isset($this->leaveVisitors[$name])) {
            return $this->leaveVisitors[$name]($node, $result);
        }

        return $result;
    }

    /**
     * Returns a map from rule names to visitation methods
     */
    private function buildVisitors()
    {
        $this->enterVisitors = [];
        $this->leaveVisitors = [];
        $refClass = new \ReflectionClass($this);
        foreach ($refClass->getMethods() as $refMethod) {
            $name = $refMethod->name;
            if (str_starts_with($name, 'leave_')) {
                $this->leaveVisitors[substr($name, 6)] = $refMethod->getClosure($this);
            } elseif (str_starts_with($name, 'enter_')) {
                $this->enterVisitors[substr($name, 6)] = $refMethod->getClosure($this);
            }
        }
    }
}
