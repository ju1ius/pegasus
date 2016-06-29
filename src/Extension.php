<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus;


abstract class Extension 
{
    abstract public function getName();

    abstract public function getLanguage();

    abstract public function getCompiler();    
}
