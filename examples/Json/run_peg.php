<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Examples\Json;

use ju1ius\Pegasus\Debug\Debug;
use ju1ius\Pegasus\GrammarFactory;
use ju1ius\Pegasus\Parser;
use Symfony\Component\Stopwatch\Stopwatch;

require_once __DIR__ . '/../../vendor/autoload.php';

$inputFile = $argv[1] ?? __DIR__ . '/test.json5';
$grammarFile = __DIR__ . '/json5.peg';

$stopwatch = new Stopwatch();
$probe = \BlackfireProbe::getMainInstance();
$stopwatch->openSection();

// ----- Runtime parser

$stopwatch->start('parse_syntax');
$grammar = GrammarFactory::fromFile($grammarFile, 2);
$stopwatch->stop('parse_syntax');
Debug::dump($grammar);
//$grammar = $grammar->tracing();

$parser = new Parser\RecursiveDescentParser($grammar);
//$parser = new Parser\Packrat($grammar);
// ----- Runtime parser

// ----- Generated parser
//$stopwatch->openSection();
//require_once __DIR__.'/../../hack/codegen/JSONParser.php';
//$parser = new \JSONParser();
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
$input = file_get_contents($inputFile);
//dump($input);
$stopwatch->stop('load_data');

// Pegasus parse
$stopwatch->start('parse_json');
//mb_regex_encoding('ASCII');
$probe->enable();
try {
    $tree = $parser->parse($input);
} catch (Parser\Exception\ParseError $err) {
    Debug::dump($parser->getTrace());
    printf("\n%s\n%s", $err->getMessage(), $err->getTraceAsString());
    exit(1);
}
$probe->disable();
$stopwatch->stop('parse_json');
//Debug::dump($parser->getTrace());
$stopwatch->start('visit');
$object = (new Json5Transform())->transform($tree);
$stopwatch->stop('visit');

$stopwatch->stopSection('script');
foreach ($stopwatch->getSectionEvents('script') as $name => $event) {
    echo $name, ': ', $event, PHP_EOL;
}
dump($object);
