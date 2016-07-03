<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace ju1ius\Pegasus\Node;

use ju1ius\Pegasus\Node;


class Terminal extends Node
{
    /**
     * @var mixed
     */
    public $value;

    /**
     * @inheritDoc
     */
    public function __construct($name, $fullText, $start, $end, $value = null)
    {
        parent::__construct($name, $fullText, $start, $end);
        $this->value = $value;
    }

    /**
     * @inheritdoc
     */
    public function terminals()
    {
        yield $this;
    }

    /**
     * @inheritdoc
     */
    public function iter()
    {
        yield $this;
    }
}
