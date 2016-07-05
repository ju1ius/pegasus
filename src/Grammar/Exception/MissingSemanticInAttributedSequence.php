<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Grammar\Exception;

use ju1ius\Pegasus\Expression\AttributedSequence;
use ju1ius\Pegasus\Expression\Semantic;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class MissingSemanticInAttributedSequence extends GrammarException
{
    /**
     * @param AttributedSequence $expr
     */
    public function __construct(AttributedSequence $expr)
    {
        $msg = sprintf(
            'Last expression of `%s` should be instance of `%s` (in rule: `%s`)',
            AttributedSequence::class,
            Semantic::class,
            $expr
        );
        parent::__construct($msg);
    }
}
