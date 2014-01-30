<?php

namespace ju1ius\Pegasus\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Twig_Loader_Filesystem;
use Twig_Environment;

use ju1ius\Pegasus\Compiler;


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
        if (!$output_dir = $input->getOption('output-dir')) {
            $output_dir = dirname($syntax_path);
        }
        if(!$name = $input->getOption('name')) {
            $fn = explode('.', basename($syntax_path))[0];
            $name = ucfirst($fn);
        }

        $compiler = new Compiler([__DIR__.'/../../../../ext']);
        $compiler->setLanguage($language);
        $language_def = $compiler->getLanguageDefinition();

        $syntax = file_get_contents($syntax_path);
        $parser_code = $compiler->compileSyntax($syntax, [
            'namespace' => $input->getOption('namespace'),
            'class' => $name,
        ]);

        if ($input->getOption('stdout')) {
            echo $parser_code, "\n";
        } else {
            file_put_contents(
                "$output_dir/$name.{$language_def['extension']}",
                $parser_code
            );
        }
    }
}
