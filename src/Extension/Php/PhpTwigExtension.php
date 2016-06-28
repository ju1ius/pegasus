<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace ju1ius\Pegasus\Extension\Php;

use Twig_Extension;
use Twig_SimpleFilter;
use Twig_SimpleFunction;


class PhpTwigExtension extends Twig_Extension
{
    public function getName()
    {
        return 'pegasus-php';
    }

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('repr', [$this, 'repr']),
            new Twig_SimpleFunction('rxrepr', [$this, 'rxrepr']),
        ];
    }

    public function getFilters()
    {
        return [
            new Twig_SimpleFilter('escape_comment', [$this, 'escapeComment'])
        ];
    }
    
    public function repr($value)
    {
        if (null === $value) {
            return 'null'; 
        } elseif (is_int($value) || is_float($value)) {
            return $value;
        } elseif (is_array($value)) {
            $out = '[';
            $first = true;
            foreach ($value as $k => $v) {
                if (!$first) $out .= ', ';
                $first = false;
                $out .= $this->repr($k) . ' => ' . $this->repr($v);
            }
            return $out . ']';
        }
        return sprintf("'%s'", addcslashes($value, "'"));
    }

    public function rxrepr($pattern)
    {
        $pattern = str_replace('\\\\', '\\\\\\\\', $pattern);
        return sprintf("'%s'", addcslashes($pattern, "'"));
    }

    public function escapeComment($value)
    {
        return str_replace('*/', '*\\/', $value);
    }
    
}
