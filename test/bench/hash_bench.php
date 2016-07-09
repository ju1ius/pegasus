<?php

class Hashable
{
    protected static $UID = 0;

    public function __construct()
    {
        $this->uid = self::$UID++;
        $this->hash = spl_object_hash($this);
    }
    public function __toString()
    {
        return get_class($this) . "#{$this->uid} ({$this->hash})";
    }
}

function xrange($start, $end=null, $step=1)
{
    if (null === $end) {
        $end = $start;
        $start = 0;
    }
    for ($i = $start; $i < $end; ++$i) {
        yield $i;
    }
}

function generate_objects($num=100)
{
    $a = [];
    for ($i = 0; $i < $num; $i++) {
        $a[] = new Hashable();
    }
    return $a;
}

function test_generate_assoc($objs)
{
    $cache = [];
    foreach (xrange(1<<16) as $pos) {
        foreach ($objs as $obj) {
            $key = $obj->hash . '@' . $pos;
            $cache[$key] = $obj;
        }
    }
    return $cache;
}

function test_generate_index($objs)
{
    $cache = [];
    foreach (xrange(1<<16) as $pos) {
        foreach ($objs as $obj) {
            $key = ($obj->uid << 30) | $pos;
            $cache[$key] = $obj;
        }
    }
    return $cache;
}

function test_fetch_assoc($objs, $cache)
{
    foreach (xrange(1<<16) as $pos) {
        foreach ($objs as $obj) {
            $key = $obj->hash . '@' . $pos;
            $cached = $cache[$key];
        }
    }
}

function test_fetch_index($objs, $cache)
{
    foreach (xrange(1<<16) as $pos) {
        foreach ($objs as $obj) {
            $key = ($obj->uid << 30) | $pos;
            $cached = $cache[$key];
        }
    }
}

$objs = generate_objects(100);

$start = microtime(true);
$assoc_cache = test_generate_assoc($objs);
$end = microtime(true);
$num_keys = count($assoc_cache);
echo "Test inserting $num_keys hash keys took ", $end - $start, " seconds\n";

$start = microtime(true);
$int_cache = test_generate_index($objs);
$end = microtime(true);
$num_keys = count($int_cache);
echo "Test inserting $num_keys integer keys took ", $end - $start, " seconds\n";

$start = microtime(true);
test_fetch_assoc($objs, $assoc_cache);
$end = microtime(true);
echo "Test fetching hash keys took ", $end - $start, " seconds\n";

$start = microtime(true);
test_fetch_index($objs, $int_cache);
$end = microtime(true);
echo "Test fetching integer keys took ", $end - $start, " seconds\n";
