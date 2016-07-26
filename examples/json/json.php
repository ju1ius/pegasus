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
use ju1ius\Pegasus\Parser\Packrat;
use ju1ius\Pegasus\CST\Transform;

require_once __DIR__ . '/../../vendor/autoload.php';


class JSONTransform extends Transform
{
    protected function leave_object($node, ...$elements)
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

    protected function leave_array($node, ...$elements)
    {
        return $elements;
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
        // let PHP figure it out !
        return 0 + $number;
    }

    protected function leave_string($node, $value)
    {
        return $value;
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
\Symfony\Component\Debug\Debug::enable();
$stopwatch = new \Symfony\Component\Stopwatch\Stopwatch();
$probe = \BlackfireProbe::getMainInstance();

// ----- Runtime parser
//$syntax = file_get_contents(__DIR__ . '/json.peg');
//
//$stopwatch->openSection();
//$stopwatch->start('parse_syntax');
//$grammar = Grammar::fromSyntax($syntax, null, 2);
//$stopwatch->stop('parse_syntax');
//
////$parser = new Packrat($grammar);
//$parser = new \ju1ius\Pegasus\Parser\RecursiveDescent($grammar);
// ----- Runtime parser

// ----- Generated parser
$stopwatch->openSection();
require_once __DIR__.'/../../hack/codegen/JSONParser.php';
$parser = new \JSONParser();
// ----- Generated parser

//$input = <<<'JSON'
//{
//    "foo": {"bar": 42},
//    "baz": [1, 2, 3],
//    "qux" : true
//}
//JSON;
//
//$input = empty($argv[1]) ? $test_input : $argv[1];
$stopwatch->start('load_data');
$input = file_get_contents('/home/ju1ius/www/embo/vhosts/www/embo/composer.lock');
$stopwatch->stop('load_data');

// Pegasus parse
$stopwatch->start('parse_json');
//mb_regex_encoding('ASCII');
$probe->enable();
$tree = $parser->parseAll($input);
$probe->disable();
$stopwatch->stop('parse_json');
$stopwatch->start('visit');
$object = (new JSONTransform())->transform($tree);
$stopwatch->stop('visit');

$stopwatch->stopSection('script');
foreach ($stopwatch->getSectionEvents('script') as $name => $event) {
    echo $name, ': ', $event, PHP_EOL;
}
