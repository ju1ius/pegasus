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
                'optimize',
                'O',
                InputOption::VALUE_REQUIRED,
                'Optimization level to apply.',
                Optimizer::LEVEL_1
            )
            ->addOption(
                'extension-dir',
                'e',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Add a directory to lookup for extensions.'
            )
            ->addOption(
                'language',
                'l',
                InputOption::VALUE_REQUIRED,
                'The language to use for the generated parser',
                'php'
            )
            ->addOption(
                'namespace',
                null,
                InputOption::VALUE_REQUIRED,
                'The namespace of the generated parser.'
            )
            ->addOption(
                'output-dir',
                'o',
                InputOption::VALUE_REQUIRED,
                'The output directory of the generated parser.',
                'php://stdout'
            )
        ;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $language = $input->getOption('language');
        $extension_dirs = array_merge(
            [__DIR__ . '/../../extensions'],
            $input->getOption('extension-dir')
        );

        $manager = new ExtensionRegistry($extension_dirs);
        $extension = $manager->getExtension($language);
        $compiler = $extension->getCompiler();

        $compilerOptions = [
            'namespace' => $input->getOption('namespace'),
            'name' => $input->getOption('name'),
            'optimization_level' => (int)$input->getOption('optimize'),
        ];

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
}
