<?php declare(strict_types=1);

use ju1ius\Pegasus\Grammar;


require_once __DIR__ . '/../../vendor/autoload.php';

$syntax = file_get_contents(__DIR__.'/nth.peg');
$grammar = Grammar::fromSyntax($syntax, 'nth', 2);
