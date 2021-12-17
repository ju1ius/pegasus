<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Expression\Terminal;

use ju1ius\Pegasus\CST\Node\Terminal;
use ju1ius\Pegasus\Parser\Parser;

class RegExp extends AbstractRegExp
{
    public function matches(string $text, Parser $parser): Terminal|bool
    {
        if (!mb_ereg_search_setpos($parser->pos)) return false;
        if ($pos = mb_ereg_search_pos($this->compiledPattern, $this->compiledFlags)) {
            [$start, $length] = $pos;
            $parser->pos += $length;
            if ($parser->isCapturing) {
                $match = mb_ereg_search_getregs();
                return new Terminal($this->name, $start, $parser->pos, $match[0]);
            }
            return true;
        }
        return false;
    }
}
