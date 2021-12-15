<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Command;

trait StandardInputReaderTrait
{
    private function readStandardInput($blockSize = 4096)
    {
        $contents = '';
        do {
            $contents .= fread(STDIN, $blockSize);
        } while (!feof(STDIN));

        return $contents;
    }
}
