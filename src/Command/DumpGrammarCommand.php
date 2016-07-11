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

use ju1ius\Pegasus\Debug\Debug;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Optimizer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class DumpGrammarCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('grammar:dump')
            ->addArgument('path', InputArgument::OPTIONAL, 'Path to a grammar file.')
            ->addOption('highlight', 'H', InputOption::VALUE_NONE, 'Show a syntax-highlighted version of the grammar')
            ->addOption('optimize', 'O', InputOption::VALUE_REQUIRED, 'Optimization level to apply.', Optimizer::LEVEL_1)
        ;
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $optimizationLevel = $input->getOption('optimize');

        if ($path = $input->getArgument('path')) {
            $syntax = file_get_contents($path);
        } else {
            $syntax = $this->askForGrammar($input, $output);
        }

        $formatter = $this->getHelper('formatter');

        if (!$syntax) {
            $output->writeln($formatter->formatBlock(['No grammar given.'], 'error'));
            return 1;
        }
        $grammar = Grammar::fromSyntax($syntax, null, $optimizationLevel);

        if ($input->getOption('highlight')) {
            Debug::highlight($grammar, $output);
        } else {
            Debug::dump($grammar, $output);
        }
    }

    private function askForGrammar(InputInterface $input, OutputInterface $output)
    {
        $lines = [];
        $numBlanks = 0;
        $input->setInteractive(true);
        $question = $this->getHelper('formatter')->formatBlock([
            '',
            'Write a set of rules and type enter.',
            'Two empty lines ends the grammar.',
            ''
        ], 'question');
        $question = new Question($question . "\n", '');

        $helper = $this->getHelper('question');
        while (true) {
            $line = $helper->ask($input, $output, $question);
            if ($line) {
                $lines[] = $line;
                $numBlanks = 0;
            } elseif (++$numBlanks === 2) {
                break;
            }
            $question = new Question('', '');
        }

        return implode("\n", $lines);
    }
}
