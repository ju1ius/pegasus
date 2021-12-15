<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Compiler;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Event\ConsoleEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class Extension
 */
abstract class Extension implements EventSubscriberInterface
{
    protected EventDispatcherInterface $dispatcher;

    abstract public function getName(): string;

    abstract public function getLanguage(): string;

    abstract public function getCompiler(): CompilerInterface;

    /**
     * @return $this
     */
    final public function setEventDispatcher(EventDispatcherInterface $dispatcher): static
    {
        $this->dispatcher = $dispatcher;
        $this->dispatcher->addSubscriber($this);

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::COMMAND => 'handleConsoleEvent',
            ConsoleEvents::TERMINATE => 'handleConsoleEvent',
            ConsoleEvents::ERROR => 'handleConsoleEvent',
        ];
    }

    final public function handleConsoleEvent(ConsoleEvent $event): void
    {
        $input = $event->getInput();
        if (!$input->hasOption('language')) return;
        if ($input->getOption('language') !== $this->getLanguage()) {
            return;
        }
        match (true) {
            $event instanceof ConsoleCommandEvent => $this->onConsoleCommand($event),
            $event instanceof ConsoleTerminateEvent => $this->onConsoleTerminate($event),
            $event instanceof ConsoleErrorEvent => $this->onConsoleError($event),
            default => null,
        };
    }

    public function onConsoleCommand(ConsoleCommandEvent $event)
    {
        //
    }

    public function onConsoleTerminate(ConsoleTerminateEvent $event)
    {
        //
    }

    public function onConsoleError(ConsoleErrorEvent $event)
    {
        //
    }
}
