<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Debug;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;

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
                // string escape sequences
                'esc' => new OutputFormatterStyle('yellow'),
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
                // Failure
                'failure' => new OutputFormatterStyle('red', null, ['bold']),
                // Success
                'success' => new OutputFormatterStyle('green', null, ['bold']),
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
