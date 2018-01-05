<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Compiler\Extension\Php\Runtime;

use ju1ius\Pegasus\CST\Node;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
final class MemoEntry
{
    /**
     * @var LeftRecursion|Node|null
     */
    public $result;

    /**
     * @var int
     */
    public $end;

    public function __construct($result, int $endPosition)
    {
        $this->result = $result;
        $this->end = $endPosition;
    }
}
