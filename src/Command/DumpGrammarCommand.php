<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Command;

use ju1ius\Pegasus\Debug\Debug;
use ju1ius\Pegasus\Grammar\OptimizationLevel;
use ju1ius\Pegasus\GrammarFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
            ->addOption('optimize', 'O', InputOption::VALUE_REQUIRED, 'Optimization level to apply.', 1);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $grammarPath = $input->getArgument('path');
        $optimizationLevel = OptimizationLevel::from((int)$input->getOption('optimize'));

        if (!$grammarPath) {
            $syntax = $this->askForGrammar($input, $output);
        } else if ($grammarPath === '-') {
            $syntax = $this->readStandardInput();
        } else {
            $syntax = file_get_contents($grammarPath);
        }

        $formatter = $this->getHelper('formatter');

        if (!$syntax) {
            $output->writeln($formatter->formatBlock(['No grammar given.'], 'error'));
            return 1;
        }

        $grammar = GrammarFactory::fromSyntax($syntax, null, $optimizationLevel);

        if ($input->getOption('highlight')) {
            Debug::highlight($grammar, $output);
        } else {
            Debug::dump($grammar, $output);
        }

        return Command::SUCCESS;
    }
}
