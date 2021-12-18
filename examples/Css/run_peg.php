<?php declare(strict_types=1);

use ju1ius\Pegasus\Grammar\OptimizationLevel;
use ju1ius\Pegasus\GrammarFactory;

require_once __DIR__ . '/../../vendor/autoload.php';

$syntax = file_get_contents(__DIR__.'/nth.peg');
$grammar = GrammarFactory::fromSyntax($syntax, 'nth', OptimizationLevel::LEVEL_2);
