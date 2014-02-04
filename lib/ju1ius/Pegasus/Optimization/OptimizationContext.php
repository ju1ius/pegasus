<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace ju1ius\Pegasus\Optimization;

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Analysis;


class OptimizationContext
{
    const TYPE_MATCHING = 1;
    const TYPE_CAPTURING = 2;

    protected static $cache = [];

    public static function create(Grammar $grammar, $type=self::TYPE_CAPTURING)
    {
        //FIXME is this really needed ?
        $key = md5(serialize([spl_object_hash($grammar), $type]));
        if (!isset(self::$cache[$key])) {
            $ctx = new OptimizationContext($grammar, $type);
            self::$cache[$key] = $ctx;
        }
        return self::$cache[$key];
    }

    public function __construct(Grammar $grammar, $type = self::TYPE_CAPTURING)
    {
        $this->grammar = $grammar;
        $this->type = $type;
        $this->analysis = new Analysis($grammar);
    }

    public function isRelevantRule($expr)
    {
        return $this->analysis->isReferenced($expr->name);
    }

    public function isInlineableRule($expr)
    {
        return $this->analysis->isRegular($expr->name);
    }

    public function getStartRule()
    {
        return $this->grammar->getStartRule()->name;
    }

    public function getAnalysis()
    {
        return $this->analysis;
    }

    public function isCapturing()
    {
        return $this->type === self::TYPE_CAPTURING;
    }
    
    public function isMatching()
    {
        return $this->type === self::TYPE_MATCHING;
    }
}
