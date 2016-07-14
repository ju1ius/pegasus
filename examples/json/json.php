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
use ju1ius\Pegasus\Traverser\NamedNodeTraverser;

require_once __DIR__ . '/../../vendor/autoload.php';


class JSONTraverser extends NamedNodeTraverser
{
    protected function leave_object($node, $elements)
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

    protected function leave_array($node, $elements)
    {
        return $elements ?: [];
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
        return trim($value, '"');
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

//require_once __DIR__.'/JSON.php';
//$parser = new JSON();
//$test_input = <<<'JSON'
//{
//    "foo": "bar",
//    "baz": [1, 2, 3],
//    "qux" : true
//}
//JSON;
//
//$input = empty($argv[1]) ? $test_input : $argv[1];
$input = file_get_contents('/home/ju1ius/www/embo/vhosts/www/embo/composer.lock');

// Pegasus parse
$start = microtime(true);
$tree = $parser->parseAll($input);
$object = (new JSONTraverser())->traverse($tree);
$end = microtime(true);
//
echo 'Pegasus', PHP_EOL;
echo '>>> Time: ', number_format(($end - $start) * 1000, 3), ' milliseconds', PHP_EOL;
echo '>>> Memory: ', memory_get_peak_usage(true), PHP_EOL;
echo '>>> Result: ';
dump($object);
