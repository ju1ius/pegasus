<?php

namespace ju1ius\Pegasus\Node;


abstract class Node implements NodeInterface
{

    public
        $expr_name,
        $full_text,
        $start,
        $end;

    /**
     * @param string $expr_name The name of the expression that generated me
     * @param string $full_text The full text fed to the parser
     * @param int    $start     The position in the text where that expr started matching
     * @param int    $end       The position after start where the expr first didn't match.
     **/
    public function __construct($expr_name, $full_text, $start, $end)
    {
        $this->expr_name = $expr_name;
        $this->full_text = $full_text;
        $this->start = $start;
        $this->end = $end;
    }

    abstract public function accept(VisitorInterface $visitor);

    public function __toString()
    {
        return $this->getText();
    }

    /**
     * Returns the text this node matched
     **/
    public function getText()
    {
        return substr($this->full_text, $this->start, $this->end - $this->start);
    }

    public function equals($other = null)
    {
        return $other !== null
            && $this->expr_name === $other->name
            && $this->full_text === $other->full_text
            && $this->start === $other->start
            && $this->end === $other->end
        ;
    }

    public function notEquals($other=null)
    {
        return !$this->equals($other);
    }

    public function leaves()
    {
        yield $this;
    }

    public function treeView($error=null)
    {
        return sprintf(
            '<%s "%s" matching "%s">%s',
            get_class($this),
            $this->expr_name ?: '',
            $this->getText(),
            $error === $this ? '  <-- *** We were here. ***' : ''
        );
    }
    
    static protected function indent($text)
    {
        return implode("\n", array_map(function($line) {
            return '    ' . $line;
        }, explode("\n", $text)));
    }
}
