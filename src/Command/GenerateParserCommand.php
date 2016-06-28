<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace ju1ius\Pegasus\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use ju1ius\Pegasus\ExtensionManager;


class GenerateParserCommand extends Command
{
    protected function configure()
    {
        $this->setName('generate:parser')
            ->setDescription('Generates a parser from a given grammar file.')
            ->addArgument(
                'grammar',
                InputArgument::REQUIRED,
                'The path to the grammar file.'
            )
            ->addOption(
                'name',
                null,
                InputOption::VALUE_REQUIRED,
                'The class name of the generated parser.'
            )
            ->addOption(
                'extension-dir',
                'e',
                InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY,
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
                'base-class',
                'c',
                InputOption::VALUE_REQUIRED,
                'The base class of the generated parser.',
                'ju1ius\Pegasus\Parser\Generated\Packrat'
            )
            ->addOption(
                'output-dir',
                'o',
                InputOption::VALUE_REQUIRED,
                'The output directory of the generated parser.'
            )
            ->addOption(
                'stdout',
                null,
                InputOption::VALUE_NONE,
                'Outputs to stdout'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $syntax_path = $input->getArgument('grammar');
        $language = $input->getOption('language');
        $extension_dirs = array_merge(
            [__DIR__.'/../../../../extensions'],
            $input->getOption('extension-dir')
        );

        if ($input->getOption('stdout')) {
            $input->setOption('output-dir', 'php://stdout');
        }

        $manager = new ExtensionManager($extension_dirs);
        $extension = $manager->getExtension($language);
        $compiler = $extension->getCompiler();

        $compiler->compileFile(
            $syntax_path,
            $input->getOption('output-dir'),
            [
                'namespace' => $input->getOption('namespace'),
                'name' => $input->getOption('name'),
            ]
        );
    }
}
