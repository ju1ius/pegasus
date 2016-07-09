<?php
require_once __DIR__ . '/../vendor/autoload.php';

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Node\Composite;
use ju1ius\Pegasus\Parser\Packrat as Parser;
use ju1ius\Pegasus\Traverser\NamedNodeTraverser;

class IniFileTraverser extends NamedNodeTraverser
{
    public function visit_config($node, $children)
    {
        $sections = [];
        foreach ($children as list($name, $properties)) {
            $sections[$name] = $properties;
        }

        return $sections;
    }

    public function visit_header($node, $children)
    {
        list($regex) = $children;

        return $regex->matches[1];
    }

    public function visit_properties($node, $children)
    {
        $props = [];
        foreach ($children as list($key, $value)) {
            $props[$key] = $value;
        }

        return $props;
    }

    public function leaveNode($node, $children)
    {
        if ($node instanceof Composite) {
            return $children
                ? 1 === count($children) ? $children[0] : $children
                : null;
        }

        return $node;
    }

}

$syntax = <<<'EOS'

config = section+

section = header properties

header = / \[ ([^\]]+) \] / ws eol

properties = (property | comment | _)*

property = key eq value eol

key = /[^=\s]+/

eq = ws '=' ws

value = /[^\n]+/

eol = /\r?\n/

comment = /\#[^\n]*\n/

ws = /[\x20\t]*/

_ = /\s*/

EOS;

$start = microtime(true);

$g = Grammar::fromSyntax($syntax);
$step_1 = microtime(true);
echo "Generating grammar: ", $step_1 - $start, "s\n";

$p = new Parser($g);
$v = new IniFileTraverser(
    [
        'ignore' => ['_', 'ws', 'eol', 'comment', 'eq'],
        'actions' => [
            'section' => 'liftChildren',
            'property' => 'liftChildren',
            'key' => 'liftValue',
            'value' => 'liftValue',
        ],
    ]
);

$text = <<<'EOS'
[section 1]
#comment 1
foo = bar

[section 2]

bar=foo
baz=w00t

EOS;

$text = $argc > 1 ? file_get_contents($argv[1]) : $text;

$step = microtime(true);
$tree = $p->parseAll($text);
$step_2 = microtime(true);
//echo "Parsing file ", $argv[1], ": ", $step_2-$step, "s\n";

$step = microtime(true);
$config = $v->traverse($tree);
$step_3 = microtime(true);
echo "Visiting parse tree: ", $step_3 - $step, "s\n";
echo "Total time: ", $step_3 - $start, "s\n";
//var_dump($config);
//echo $tree->inspect(), "\n";
