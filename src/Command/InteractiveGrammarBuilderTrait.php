<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * @author ju1ius
 */
trait InteractiveGrammarBuilderTrait
{
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
