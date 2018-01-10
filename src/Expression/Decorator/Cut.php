<?php declare(strict_types=1);


namespace ju1ius\Pegasus\Expression\Decorator;

use ju1ius\Pegasus\Expression\Decorator;
use ju1ius\Pegasus\Parser\Parser;


/**
 * Cut expressions make a parser commit to a particular option after certain tokens have been seen.
 *
 * They make parsing more efficient, because other options are not tried.
 * They also make error messages more precise, because errors will be reported
 * closest to the point of failure in the input.
 */
final class Cut extends Decorator
{
    public function match(string $text, Parser $parser)
    {
        $pos = $parser->pos;
        $result = $this->children[0]->match($text, $parser);
        if ($result) {
            $parser->cut($pos);
        }

        return $result;
    }

    public function __toString(): string
    {
        return sprintf('%s^', $this->stringChildren()[0]);
    }
}