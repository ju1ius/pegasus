<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Command;

use ju1ius\Pegasus\Compiler\ExtensionRegistry;
use ju1ius\Pegasus\Compiler\Twig\Extension\PegasusTwigExtension;
use ju1ius\Pegasus\Grammar;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class GenerateVisitorCommand extends Command
{

    /**
     * @var ExtensionRegistry
     */
    private $registry;

    public function __construct(ExtensionRegistry $registry, ?string $name = null)
    {
        parent::__construct($name);
        $this->registry = $registry;
    }

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

    protected function execute(InputInterface $input, OutputInterface $output): int
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

        $loader = new FilesystemLoader([$tpl_dir]);
        $twig = new Environment($loader, ['autoescape' => false]);
        $twig->addExtension(new PegasusTwigExtension());

        $visitor_code = $twig->render('node_visitor.twig', ['grammar' => $grammar]);
        file_put_contents(
            "{$output_dir}/{$name}.{$language}",
            $visitor_code
        );

        return Command::SUCCESS;
    }
    
}
