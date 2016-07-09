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

use ju1ius\Pegasus\ExtensionRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class GenerateParserCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('generate:parser')
            ->setDescription('Generates a parser from a grammar file, or interactively.')
            ->addOption(
                'grammar',
                'g',
                InputOption::VALUE_REQUIRED,
                'Path to a grammar file.'
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

        $manager = new ExtensionRepository($extension_dirs);
        $extension = $manager->getExtension($language);
        $compiler = $extension->getCompiler();

        $compilerOptions = [
            'namespace' => $input->getOption('namespace'),
            'name' => $input->getOption('name'),
        ];

        if (!$input->getOption('grammar')) {
            $input->setInteractive(true);
            //$output->writeln('<info>Write rules, and type enter. An empty line ends the grammar.</info>');
            $helper = $this->getHelper('question');
            $question = new Question("<info>Write a rule and type enter. An empty line ends the grammar.</info>\n", '');
            $rules = [];
            while ($rule = $helper->ask($input, $output, $question)) {
                $rules[] = $rule;
            }
            $compiler->compileSyntax(
                implode("\n", $rules),
                $input->getOption('output-dir'),
                $compilerOptions
            );
        } else {
            $syntax_path = $input->getOption('grammar');
            $compiler->compileFile(
                $syntax_path,
                $input->getOption('output-dir'),
                $compilerOptions
            );
        }
    }
}
