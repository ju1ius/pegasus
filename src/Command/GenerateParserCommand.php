<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\Command;

use ju1ius\Pegasus\Compiler\ExtensionRegistry;
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

    /**
     * @var ExtensionRegistry
     */
    private $registry;

    public function __construct(ExtensionRegistry $registry, ?string $name = null)
    {
        parent::__construct($name);
        $this->registry = $registry;
    }

    /**
     * @inheritdoc
     */
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
                Optimizer::LEVEL_1
            )
            ->addOption(
                'no-cache',
                null,
                InputOption::VALUE_NONE,
                'Disables the Packrat cache if the grammar is not left-recursive.',
                false
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

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
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
    }

    protected function getCompilerOptions(InputInterface $input)
    {
        $options = $input->getOptions();
        $excluded = [
            'language', 'extension-dir', 'output-dir',
            'help', 'quiet', 'verbose', 'version',
            'ansi', 'no-ansi', 'no-interaction',
        ];

        return array_filter($options, function ($name) use ($excluded) {
            return !in_array($name, $excluded);
        }, ARRAY_FILTER_USE_KEY);
    }
}
