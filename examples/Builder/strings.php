<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Examples\Builder;

use ju1ius\Pegasus\CST\Transform;
use ju1ius\Pegasus\Debug\Debug;
use ju1ius\Pegasus\Grammar\Optimizer;
use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\Parser\RecursiveDescentParser;
use Symfony\Component\VarDumper\VarDumper;

require_once __DIR__ . '/../../vendor/autoload.php';

const GRAMMAR = <<<'PEG'
@grammar Strings
string          = q:quote_char content $q
content         = ~(escaped_char | !($q) .)*
quote_char      = "'" | '"'
escaped_char    = "\" .
PEG;

const EXAMPLE = <<<'EXAMPLE'
"A doubly-quoted (\") string!"
EXAMPLE;


$grammar = GrammarBuilder::create('strings')
    ->rule('string')->sequence()
        ->bindTo('q')->reference('quote_char')
        ->reference('content')
        ->backReference('q')
    ->rule('content')->asToken()->zeroOrMore()->oneOf()
        ->reference('escaped_char')
        ->sequence()
            ->not()->backReference('q')
            ->any()
    ->rule('quote_char')->oneOf()
        ->literal('"')
        ->literal("'")
    ->rule('escaped_char')->sequence()
        ->literal('\\')
        ->any()
    ->getGrammar();

Debug::dump($grammar);

$parser = new RecursiveDescentParser($grammar);
$cst = $parser->parse($argv[1] ?? EXAMPLE);
$transform = new class extends Transform {
    public function leave_string($node, $openQuote, $content, $closeQuote): string
    {
        return $content;
    }
    public function leave_escaped_char($node, $escape, $char): string
    {
        return $char;
    }
};
$string = $transform->transform($cst);
Debug::dump($string);
Debug::dump($cst);
VarDumper::dump('\\');
