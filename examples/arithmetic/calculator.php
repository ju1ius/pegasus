<?php
require_once __DIR__.'/../../vendor/autoload.php';

use ju1ius\Pegasus\Debug\Debug;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Parser\LeftRecursivePackrat;
use ju1ius\Pegasus\Traverser\NamedNodeTraverser;

$SYNTAX = file_get_contents(__DIR__ . '/arithmetic.peg');

final class Calculator extends NamedNodeTraverser
{
    /**
     * @inheritDoc
     */
    protected function beforeTraverse(\ju1ius\Pegasus\Node $node)
    {
        return;
    }

    protected function leave_Add($node, $lhs, $rhs)
    {
        return $lhs + $rhs;
    }

    protected function leave_Substract($node, $lhs, $rhs)
    {
        return $lhs - $rhs;
    }

    protected function leave_Multiply($node, $lhs, $rhs)
    {
        return $lhs * $rhs;
    }

    protected function leave_Divide($node, $lhs, $rhs)
    {
        return $lhs / $rhs;
    }

    protected function leave_int($node, $value)
    {
        return (int) $value;
    }

    protected function leave_float($node, $value)
    {
        return (float) $value;
    }

    protected function leave_expo($node, $value)
    {
        return (float) $value;
    }
}

//$meta = MetaGrammar::create();
//$parser = new LeftRecursivePackrat($meta->fold());
//$tree = $parser->parseAll($syntax);
//Debug::dump($tree);
//$t = new MetaGrammarTraverser();
//$grammar = $t->traverse($tree);

$grammar = Grammar::fromSyntax($SYNTAX);
//echo $grammar, "\n";
//Debug::dump($grammar);

$parser = new LeftRecursivePackrat($grammar);
$input = '3 * 12 / 24 - 7.2';
if (!empty($argv[1])) {
    $input = $argv[1];
}
$tree = $parser->parseAll($input);
//Debug::dump($tree);
$calculator = new Calculator();
$result = $calculator->traverse($tree);
echo ">>> ", $result, "\n";
