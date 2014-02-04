<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace ju1ius\Pegasus\Twig;


class DataCollector
{
    protected $data = [];

    public function collect($key, $data)
    {
        $this->data[$key][] = $data;
    }

    public function retrieve($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : '';
    }

    public function clear($key = null)
    {
        if (null === $key) {
            $this->data = [];
        } else {
            unset($this->data[$key]);
        }
    }
}
