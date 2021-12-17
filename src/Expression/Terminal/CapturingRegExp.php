<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Expression\Terminal;

use ju1ius\Pegasus\CST\Node\Terminal;
use ju1ius\Pegasus\Parser\Parser;

class CapturingRegExp extends AbstractRegExp
{
    public function matches(string $text, Parser $parser): Terminal|bool
    {
        $start = $parser->pos;
        if (preg_match($this->compiledPattern, $text, $matches, 0, $start)) {
            $match = $matches[0];
            $end = $parser->pos += \strlen($match);
            if ($parser->isCapturing) {
                return new Terminal($this->name, $start, $end, $match, ['groups' => $matches]);
            }
            return true;
        }
        return false;
    }
}
