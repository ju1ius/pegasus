<?php

class Foo
{
    public function __construct()
    {
        $this->id = spl_object_hash($this);
    }
}

$num = 1e5;
$objs = [];
for ($i = 0; $i < $num; $i++) {
    $f = new Foo;
    $objs[$f->id] = $f;
}

$arr = [];
$mem_empty = memory_get_usage();

$start = microtime(true);
// Arrays
foreach ($objs as $obj) {
    $arr[spl_object_hash($obj)] = $obj;
}
$end = microtime(true);
$time_to_fill = $end - $start;

$start = microtime(true);
foreach ($objs as $obj) {
    $r = isset($arr[spl_object_hash($obj)]);
}
$end = microtime(true);
$time_to_search = $end - $start;

$start = microtime(true);
foreach ($objs as $obj) {
    $r = $arr[spl_object_hash($obj)];
}
$end = microtime(true);
$time_to_get = $end - $start;

$mem_used = memory_get_usage() - $mem_empty;

echo sprintf("
Array
=====
Time to fill:   %0.12f
Time to check:  %0.12f
Time to get:    %0.12f
Memory:         %d
", $time_to_fill, $time_to_search, $time_to_get, $mem_used);

unset($arr);

//SplObjectStorage
$sos = new SplObjectStorage();
$mem_empty = memory_get_usage();

$start = microtime(true);
foreach ($objs as $obj) {
    $sos->attach($obj);
}
$end = microtime(true);
$time_to_fill = $end - $start;

$start = microtime(true);
foreach ($objs as $obj) {
    $r = $sos->contains($obj);
}
$end = microtime(true);
$time_to_search = $end - $start;

$start = microtime(true);
foreach ($objs as $obj) {
    $r = $sos[$obj];
}
$end = microtime(true);
$time_to_get = $end - $start;

$mem_used = memory_get_usage() - $mem_empty;

echo sprintf("
SplObjectStorage
================
Time to fill:   %s
Time to check:  %s
Time to get:    %s
Memory:         %d
", $time_to_fill, $time_to_search, $time_to_get, $mem_used);
