<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Compiler\Extension\Php;

use ju1ius\Pegasus\Command\GenerateParserCommand;
use ju1ius\Pegasus\Compiler\CompilerInterface;
use ju1ius\Pegasus\Compiler\Extension;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputOption;

class PhpExtension extends Extension
{
    public function getName(): string
    {
        return 'php';
    }

    public function getLanguage(): string
    {
        return 'php';
    }

    public function getCompiler(): CompilerInterface
    {
        return new PhpCompiler();
    }

    public function onConsoleCommand(ConsoleCommandEvent $event): void
    {
        $command = $event->getCommand();
        $def = $command->getDefinition();
        if ($command instanceof GenerateParserCommand) {
            $def->addOption(new InputOption(
                'namespace', 'N',
                InputOption::VALUE_REQUIRED,
                'The namespace of the generated parser.'
            ));
        }
    }
}
