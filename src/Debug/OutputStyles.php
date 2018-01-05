<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Debug;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
final class OutputStyles
{
    private static $outputStyles = null;

    /**
     * @return OutputFormatterStyle[]
     */
    public static function getOutputStyles(): array
    {
        if (self::$outputStyles === null) {
            self::$outputStyles = [
                // delimiter
                'd' => new OutputFormatterStyle('white'),
                // symbols (@, &, !, ...)
                'sym' => new OutputFormatterStyle('magenta'),
                // quantifiers
                'q' => new OutputFormatterStyle('magenta'),
                // terminals
                'term' => new OutputFormatterStyle('green'),
                // rule
                'rule' => new OutputFormatterStyle('blue', null, ['bold']),
                // directive
                'directive' => new OutputFormatterStyle('yellow', null, ['bold']),
                // reference
                'ref' => new OutputFormatterStyle('cyan'),
                // keyword (EOF, FAIL, ...)
                'kw' => new OutputFormatterStyle('cyan'),
                // labels
                'label' => new OutputFormatterStyle('cyan'),
                // name
                'id' => new OutputFormatterStyle('cyan'),
                // class
                'class' => new OutputFormatterStyle('red', null, ['bold']),
            ];
        }

        return self::$outputStyles;
    }

    public static function setOutputStyles(OutputInterface $output): OutputInterface
    {
        $fmt = $output->getFormatter();
        foreach (self::getOutputStyles() as $name => $style) {
            $fmt->setStyle($name, $style);
        }

        return $output;
    }
}
