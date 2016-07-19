<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\RegExp\Exception;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class UnmatchedClosingParenthesis extends \LogicException
{

    /**
     * @param string $pattern
     * @param int    $offset
     */
    public function __construct($pattern, $offset)
    {
        parent::__construct(sprintf(
            'Unmatched closing parenthesis at offset %d in pattern `%s`',
            $offset,
            $pattern
        ));
    }
}
