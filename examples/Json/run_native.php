<?php declare(strict_types=1);

use ju1ius\Pegasus\Examples\Json\JsonTransform;
use ju1ius\Pegasus\Parser;
use Symfony\Component\Stopwatch\Stopwatch;

require_once __DIR__ . '/../../vendor/autoload.php';

\Symfony\Component\Debug\Debug::enable();
$stopwatch = new Stopwatch();
$probe = \BlackfireProbe::getMainInstance();

$stopwatch->openSection();

$stopwatch->start('load_grammar');
$grammar = require_once __DIR__.'/JsonGrammar.php';
$stopwatch->stop('load_grammar');

// ----- Load data
$stopwatch->start('load_data');
$input = file_get_contents('/home/ju1ius/w3/embo/vhosts/lembobineuse.biz/composer.lock');
$stopwatch->stop('load_data');

// ----- Pegasus parse
$parser = new Parser\RecursiveDescent($grammar);
//$parser = new Parser\Packrat($grammar);

$stopwatch->start('parse_json');
$probe->enable();
$tree = $parser->parseAll($input);
$probe->disable();
$stopwatch->stop('parse_json');

// ----- Transform
$stopwatch->start('visit');
$object = (new JsonTransform())->transform($tree);
$stopwatch->stop('visit');

// ----- Collect timings
$stopwatch->stopSection('script');
foreach ($stopwatch->getSectionEvents('script') as $name => $event) {
    echo $name, ': ', $event, PHP_EOL;
}