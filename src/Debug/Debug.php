<?php declare(strict_types=1);

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

final class Debug
{
    /**
     * @param mixed $value
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
     * @throws Grammar\Exception\SelfReferencingRule
     */
    public static function highlight(Grammar|Expression $value, OutputInterface $output = null)
    {
        if (!$output) {
            $output = self::createConsoleOutput();
        } else {
            OutputStyles::setOutputStyles($output);
        }

        if ($value instanceof Grammar) {
            GrammarHighlighter::highlight($value, $output);
        } else {
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
