<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Debug;

use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Node;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
final class Debug
{
    /**
     * @param mixed           $value
     * @param OutputInterface $output
     */
    public static function dump($value, OutputInterface $output = null)
    {
        if (!$output) {
            $output = self::createConsoleOutput();
        } else {
            OutputStyles::setOutputStyles($output);
        }

        if ($value instanceof Grammar) {
            GrammarDumper::dump($value, $output);
        } elseif ($value instanceof Expression) {
            ExpressionDumper::dump($value, $output);
        } elseif ($value instanceof Node) {
            ParseTreeDumper::dump($value, $output);
        } else {
            dump($value);
        }
    }

    /**
     * @param Grammar|Expression   $value
     * @param OutputInterface|null $output
     */
    public static function highlight($value, OutputInterface $output = null)
    {
        if (!$value instanceof Grammar && !$value instanceof Expression) {
            throw new \InvalidArgumentException(sprintf(
                'Can only highlight grammars or expressions.'
            ));
        }
        if ($value instanceof Grammar) {
            $value = GrammarHighlighter::highlight($value);
        } elseif ($value instanceof Expression) {
            $value = ExpressionHighlighter::highlight($value);
        }

        if (!$output) {
            $output = self::createConsoleOutput();
        } else {
            OutputStyles::setOutputStyles($output);
        }

        $output->writeln($value);
    }

    /**
     * @return ConsoleOutput
     */
    public static function createConsoleOutput()
    {
        $output = new ConsoleOutput();
        $output->setFormatter(new OutputFormatter(true));
        OutputStyles::setOutputStyles($output);

        return $output;
    }

    /**
     * @return BufferedOutput
     */
    public static function createBufferedOutput()
    {
        $output = new BufferedOutput();
        $output->setFormatter(new OutputFormatter(true));
        OutputStyles::setOutputStyles($output);

        return $output;
    }
}
