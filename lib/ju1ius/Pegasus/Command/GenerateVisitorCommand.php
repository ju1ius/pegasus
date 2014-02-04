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

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Compiler\PegasusTwigExtension;


class GenerateVisitorCommand extends Command
{
    protected function configure()
    {
        $this->setName('generate:visitor')
            ->setDescription('Generates a node visitor from a given grammar file.')
            ->addArgument(
                'grammar',
                InputArgument::REQUIRED,
                'The path to the grammar file.'
            )
            ->addOption(
                'name',
                'n',
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
                'The output directory of the generated parser.'
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
        $name = $input->getOption('name');
        $tpl_dir = __DIR__ . '/../Compiler/templates/' . $language;

        $syntax = file_get_contents($syntax_path);
        $grammar = Grammar::fromSyntax($syntax);

        $loader = new Twig_Loader_Filesystem([$tpl_dir]);
        $twig = new Twig_Environment($loader, ['autoescape' => false]);
        $twig->addExtension(new PegasusTwigExtension);

        $visitor_tpl = $twig->loadTemplate('node_visitor.twig');
        $visitor_code = $visitor_tpl->render(['grammar' => $grammar]);
        file_put_contents(
            "{$output_dir}/{$name}.{$language}",
            $visitor_code
        );
    }
    
}
