<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Command;

use ju1ius\Pegasus\Compiler\ExtensionRegistry;
use ju1ius\Pegasus\Grammar\OptimizationLevel;
use ju1ius\Pegasus\Grammar\Optimizer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateParserCommand extends Command
{
    use InteractiveGrammarBuilderTrait;
    use StandardInputReaderTrait;

    public function __construct(
        private ExtensionRegistry $registry,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('generate:parser')
            ->setDescription('Generates a parser from a grammar file, or interactively.')
            ->addArgument(
                'grammar',
                InputArgument::OPTIONAL,
                'Path to a grammar file. Pass - to read from STDIN or ommit for interactive grammar input.'
            )
            ->addOption(
                'name',
                null,
                InputOption::VALUE_REQUIRED,
                'The class name of the generated parser.'
            )
            ->addOption(
                'language',
                'l',
                InputOption::VALUE_REQUIRED,
                'The language to use for the generated parser',
                'php'
            )
            ->addOption(
                'output-dir',
                'o',
                InputOption::VALUE_REQUIRED,
                'The output directory of the generated parser.',
                'php://stdout'
            )
            ->addOption(
                'optimization-level',
                'O',
                InputOption::VALUE_REQUIRED,
                'Optimization level to apply.',
                1
            )
            ->addOption(
                'no-cache',
                null,
                InputOption::VALUE_NONE,
                'Disables the Packrat cache if the grammar is not left-recursive.',
            )
            ->addOption(
                'extension-dir',
                'e',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Add a directory to lookup for extensions.'
            )
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $extension_dirs = array_merge(
            [__DIR__ . '/../../extensions'],
            $input->getOption('extension-dir')
        );
        $this->registry->addDirectory(...$extension_dirs);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $language = $input->getOption('language');
        $extension = $this->registry->getExtension($language);
        $compiler = $extension->getCompiler();

        $compilerOptions = $this->getCompilerOptions($input);
        $grammarPath = $input->getArgument('grammar');

        if (!$grammarPath || $grammarPath === '-') {
            if (!$grammarPath) {
                $syntax = $this->askForGrammar($input, $output);
            } else {
                $syntax = $this->readStandardInput();
            }
            $code = $compiler->compileSyntax($syntax, $compilerOptions);
        } else {
            $code = $compiler->compileFile($grammarPath, $compilerOptions);
        }

        // TODO: write to files !
        echo $code . PHP_EOL;

        return Command::SUCCESS;
    }

    protected function getCompilerOptions(InputInterface $input): array
    {
        $options = $input->getOptions();
        $excluded = [
            'language', 'extension-dir', 'output-dir',
            'help', 'quiet', 'verbose', 'version',
            'ansi', 'no-ansi', 'no-interaction',
        ];
        $level = $input->getOption('optimization-level') ?? 1;
        $options['optimization-level'] = OptimizationLevel::from((int)$level);

        return array_filter($options, fn($name) => !in_array($name, $excluded), ARRAY_FILTER_USE_KEY);
    }
}
