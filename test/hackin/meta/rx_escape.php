<?php

$p1 = '/\/((?:(?:\\\\.)|[^\/])*)\/([ilmSux]*)?/';
$p2 = <<<'EOS'
/ \/ ((?: (?:\\.) | [^\/] )*) \/ ([ilmsux]*)? /
EOS;
preg_match($p1, $p2, $matches);
var_dump($matches);
$p3 = sprintf('/\G %s /x', $matches[1]);
$p4 = <<<'EOS'
/ \/ ((?: (?:\\.) | [^\/] )*) \/ ([ilmsux]*)? /
EOS;
preg_match($p3, $p4, $matches);
var_dump($matches);
