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

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Trace\Trace;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\VarDumper\VarDumper;


/**
 * @author ju1ius <ju1ius@laposte.net>
 */
final class Debug
{
    /**
     * @param mixed $value
     * @param OutputInterface $output
     * @throws Grammar\Exception\SelfReferencingRule
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
            CSTDumper::dump($value, $output);
        } elseif ($value instanceof Trace) {
            TraceDumper::dump($value, $output);
        } else {
            VarDumper::dump($value);
        }
    }

    /**
     * @param Grammar|Expression $value
     * @param OutputInterface|null $output
     * @throws Grammar\Exception\SelfReferencingRule
     */
    public static function highlight($value, OutputInterface $output = null)
    {
        if (!$value instanceof Grammar && !$value instanceof Expression) {
            throw new \InvalidArgumentException(sprintf(
                'Can only highlight grammars or expressions.'
            ));
        }

        if (!$output) {
            $output = self::createConsoleOutput();
        } else {
            OutputStyles::setOutputStyles($output);
        }

        if ($value instanceof Grammar) {
            GrammarHighlighter::highlight($value, $output);
        } elseif ($value instanceof Expression) {
            ExpressionHighlighter::highlight($value, $output);
        }
    }

    public static function createConsoleOutput(): ConsoleOutput
    {
        $output = new ConsoleOutput();
        $output->setFormatter(new OutputFormatter(true));
        OutputStyles::setOutputStyles($output);

        return $output;
    }

    public static function createBufferedOutput(): BufferedOutput
    {
        $output = new BufferedOutput();
        $output->setFormatter(new OutputFormatter(true));
        OutputStyles::setOutputStyles($output);

        return $output;
    }
}
