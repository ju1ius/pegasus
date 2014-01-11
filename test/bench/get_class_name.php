<?php

function get_class_name($fqn)
{
    if ((false === $i = strrpos($fqn, '\\'))
        || (false === $name = substr($fqn, $i+1))
    ) {
        return $fqn;
    }
    return $name;
}

function get_class_name2($fqn)
{
    $a = explode('\\', $fqn);
    return end($a);
}
function get_class_name3($fqn)
{
    if (preg_match('/\\\\(\w+)$/', $fqn, $match)) {
        return $match[1];
    }
    return $fqn;
}

$num = 1e5;
$fqn = 'ju1ius\Pegasus\Expression\Composite\Foo';
//echo get_class_name($fqn), "\n";
//exit();

$start = microtime(true);
for ($i = 0; $i < $num; $i++) {
    $n = get_class_name($fqn);
}
$step_1 = microtime(true);

for ($i = 0; $i < $num; $i++) {
    $n = get_class_name2($fqn);
}
$step_2 = microtime(true);

for ($i = 0; $i < $num; $i++) {
    $n = get_class_name3($fqn);
}
$step_3 = microtime(true);

echo sprintf("get_class_name (substr): %ss\n", $step_1 - $start);
echo sprintf("get_class_name2 (explode): %ss\n", $step_2 - $step_1);
echo sprintf("get_class_name3 (regex): %ss\n", $step_3 - $step_2);
