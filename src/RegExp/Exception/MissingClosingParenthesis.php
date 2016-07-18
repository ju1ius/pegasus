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
 * A regexp pattern contains unbalanced parentheses.
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
final class MissingClosingParenthesis extends \LogicException
{
    public function __construct($pattern)
    {
        parent::__construct(sprintf(
            'The following pattern is missing a closing parenthesis: `%s`',
            $pattern
        ));
    }
}
