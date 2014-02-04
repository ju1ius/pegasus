<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace ju1ius\Pegasus\Utils;


class LineCounter
{
    const NL = "\n";
    const TAB = "\t";

    protected
        $source,
        $tabsize,
        $index = 0,
        $line = 0,
        $column = 0;

    public function __construct($source, $tabsize=4)
    {
        $this->source = $source;
        $this->tabsize = $tabsize;
    }

    public function line($index)
    {
        $this->count($index);
        return $this->line;
    }

    public function column($index)
    {
        $this->count($index);
        return $this->column;
    }

    protected function count($index)
    {
        if ($this->index === $index) return;
        $this->index = $index;
        $this->line = 1;
        $i = $p = 0;
        while (($i = strpos($this->source, self::NL, $p)) && ($i < $index)) {
            $this->line++;
            $p = $i + 1;
        }
        $this->column = 1;
        for ($i = $p; $i < $index; $i++) {
            $this->column = self::TAB === $this->source[$i]
                ? $this->next_tab_stop()
                : $this->column + 1;
        }
    }

    protected function next_tab_stop()
    {
        return (($this->column - 1) / $this->tabsize + 1) * $this->tabsize + 1;
    }
}
