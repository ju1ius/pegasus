<?php
require_once __DIR__.'/utils.php';

use ju1ius\Pegasus\MetaGrammar;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Expression\Composite;
use ju1ius\Pegasus\Expression\OneOf;
use ju1ius\Pegasus\Expression\OneOrMore;
use ju1ius\Pegasus\Expression\Reference as Ref;
use ju1ius\Pegasus\Expression\Sequence;
use ju1ius\Pegasus\Expression\Literal;

use ju1ius\Pegasus\Visitor\ExpressionTraverser;
use ju1ius\Pegasus\Visitor\ExpressionTraverserInterface;
use ju1ius\Pegasus\Visitor\ExpressionVisitorInterface;
use ju1ius\Pegasus\Visitor\RefMaker;
//use ju1ius\Pegasus\Visitor\ExpressionCloner;
//

class UnsafeTraverser implements ExpressionTraverserInterface
{
	/**
	 * @var ExpressionVisitor[]
	 */
	protected $visitors;

	/**
	 * Keeps track of the traversed expression to avoid infinite recursion
	 * with recursive rules.
	 *
	 * @var array
	 */	
	protected $visited = [];

	public function __construct()
	{
		$this->visitors = [];
	}

	/**
	 * {@inheritDoc}
	 */
	public function addVisitor(ExpressionVisitorInterface $visitor)
	{
		$this->visitors[] = $visitor;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function removeVisitor(ExpressionVisitorInterface $visitor)
	{
		foreach ($this->visitors as $index => $storedVisitor) {
			if ($storedVisitor === $visitor) {
				unset($this->visitors[$index]);
				break;
			}
		}

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function traverse(Expression $expr)
	{
        //$this->visited = [];
        //$this->visited = new \SplObjectStorage();

		foreach ($this->visitors as $visitor) {
			if (null !== $result = $visitor->beforeTraverse($expr)) {
				$expr = $result;
			}
		}

        if(null !== $result = $this->traverseExpression($expr)) {
            $expr = $result;
        }

		foreach ($this->visitors as $visitor) {
			if (null !== $result = $visitor->afterTraverse($expr)) {
				$expr = $result;
			}
		}

		return $expr;
	}

	protected function traverseExpression(Expression $expr)
    {
		foreach ($this->visitors as $visitor) {
			if (null !== $result = $visitor->enterNode($expr)) {
				$expr = $result;
			}
		}

		if ($expr instanceof Composite) {
			foreach ($expr->children as $i => $child) {
				// protect against recursive rules
                //if (isset($this->visited[$child->id])) {
                    //continue;
                //}
                //$this->visited[$child->id] = true;

				if (null !== $result = $this->traverseExpression($child)) {
                    $expr->children[$i] = $result;
                    //$child = $result;
				}
			}
		}

		foreach ($this->visitors as $visitor) {
			if (null !== $result = $visitor->leaveNode($expr)) {
				$expr = $result;
			}
		}

		return $expr;
	}
}

$term = new Literal('w00w', 'term');
$terms = new Sequence([], 'terms');
$terms->children = [$terms, $term];
$choice = new Sequence([
    $terms,
    new OneOrMore([
        new Sequence([
            new Literal('-'),
            $terms
        ])
    ])
], 'choice');

$metagrammar = MetaGrammar::getGrammar();
$start = $metagrammar['grammar'];

print_expr_tree($start);
//xdebug_var_dump($choice);
echo "\n\nCLONING...\n\n";

$trav = (new UnsafeTraverser)
    ->addVisitor(new ExpressionCloner)
    ->addVisitor(new RefMaker($metagrammar))
    ->addVisitor(new ExprTreePrinter)
;
$new_g = new Grammar();
foreach ($metagrammar as $expr) {
    $new = $trav->traverse($expr);
    $new_g[$new->name] = $new;
}
$new_g->finalize();

echo $new_g, "\n";
foreach ($new_g as $name => $expr) {
    $old_expr = $metagrammar[$name];
    $old_h = spl_object_hash($old_expr);
    $new_h = spl_object_hash($expr);
    assert($new_h !== $old_h, "Expression $name wasn't cloned correctly !");
}

//print_expr_tree($start);

//assert($start !== $metagrammar['grammar']);
