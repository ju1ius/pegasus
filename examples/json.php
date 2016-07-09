<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Parser\LeftRecursivePackrat;
use ju1ius\Pegasus\Traverser\NamedNodeTraverser;

require_once __DIR__ . '/../vendor/autoload.php';

$SYNTAX = <<<'SYNTAX'
%name JSON

json        = _ (object | array) EOF
object      = ~'{' _ members? ~'}' _
members     = pair (~',' _ pair)*
pair        = string ~':' _ value
array       = ~'[' _ elements? ~']' _
elements    = value (~',' _ value)*

value       = object
            | array
            | string
            | number
            | 'true' _    <= true
            | 'false' _   <= false
            | 'null' _    <= null
            
string      = /"(?:\\.|[^"])*"/ _
number      = @(int frac? expo?) _

int         = /-?(?:[1-9]\d*|0(?!\d))/
frac        = /\.\d+/
expo        = /[eE][+-]?\d+/

_           = ~/\s*/
SYNTAX;

class Json extends NamedNodeTraverser
{
    protected function leave_object($node, $elements)
    {
        return $elements ?: [];
    }

    protected function leave_members($node, $first, $others)
    {
        $assoc = [$first[0] => $first[1]];
        foreach ($others as list($key, $value)) {
            $assoc[$key] = $value;
        }

        return $assoc;
    }

    protected function leave_array($node, $elements)
    {
        return $elements ?: [];
    }

    protected function leave_elements($node, $first, $others)
    {
        if (!$others) {
            return [$first];
        }
        return array_merge([$first], $others);
    }

    protected function leave_number($node, $number)
    {
        return (float)$number;
    }

    protected function leave_string($node, $value)
    {
        return trim($value, '"');
    }

    protected function leave_null($node, $value)
    {
        return null;
    }

    protected function leave_true($node, $value)
    {
        return true;
    }

    protected function leave_false($node, $value)
    {
        return false;
    }
}

$grammar = Grammar::fromSyntax($SYNTAX)->fold();
//\ju1ius\Pegasus\Debug\Debug::dump($grammar);
$parser = new LeftRecursivePackrat($grammar);

$test_input = <<<'JSON'
{
    "foo": "bar",
    "baz": [1, 2, 3],
    "qux" : []
}
JSON;

$input = empty($argv[1]) ? $test_input : $argv[1];

// Pegasus parse

$start = microtime(true);
$tree = $parser->parseAll($input);
$object = (new Json())->traverse($tree);
$end = microtime(true);

echo 'Pegasus', PHP_EOL;
echo '>>> Time: ', number_format(($end - $start) * 1000, 3), ' milliseconds', PHP_EOL;
echo '>>> Result: ';
//var_dump($object);

// Native parse

$start = microtime(true);
$object = json_decode($input, true);
$end = microtime(true);

echo PHP_EOL, 'json_decode', PHP_EOL;
echo '>>> Time: ', number_format(($end - $start) * 1000, 3), ' milliseconds', PHP_EOL;
echo '>>> Result: ';
//var_dump($object);