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
            ->addOption('highlight', 'H', InputOption::VALUE_NONE, 'Show a syntax-highlighted version of the grammar');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($path = $input->getArgument('path')) {
            $grammar = Grammar::fromSyntax(file_get_contents($path));
        } else {
            $grammar = Grammar::fromSyntax($this->askForGrammar($input, $output));
        }

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
        $helper = $this->getHelper('question');
        $question = new Question(
            "<question>Write a set of rules and type enter.\nTwo empty lines ends the grammar.</question>\n",
            ''
        );
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
