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

use ju1ius\Pegasus\Debug\Debug;
use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Grammar\Optimizer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class DumpGrammarCommand extends Command
{
    use InteractiveGrammarBuilderTrait;
    use StandardInputReaderTrait;

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('grammar:dump')
            ->addArgument(
                'path',
                InputArgument::OPTIONAL,
                'Path to a grammar file. Pass - to read from STDIN or ommit for interactive grammar input.'
            )
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
        $grammarPath = $input->getArgument('path');

        if (!$grammarPath) {
            $syntax = $this->askForGrammar($input, $output);
        } elseif ($grammarPath === '-') {
            $syntax = $this->readStandardInput();
        } else {
            $syntax = file_get_contents($grammarPath);
        }

        $formatter = $this->getHelper('formatter');

        if (!$syntax) {
            $output->writeln($formatter->formatBlock(['No grammar given.'], 'error'));
            return 1;
        }
        $grammar = Grammar::fromSyntax($syntax, null, (int)$optimizationLevel);

        if ($input->getOption('highlight')) {
            Debug::highlight($grammar, $output);
        } else {
            Debug::dump($grammar, $output);
        }
    }
}
