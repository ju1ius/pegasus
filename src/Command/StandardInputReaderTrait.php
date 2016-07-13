<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Command;

/**
 * @author ju1ius
 */
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
