<?php declare(strict_types=1);
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
 *
 * @author ju1ius <ju1ius@laposte.net>
 */
abstract class Extension implements EventSubscriberInterface
{
    protected $dispatcher;

    abstract public function getName(): string;

    abstract public function getLanguage(): string;

    abstract public function getCompiler(): CompilerInterface;

    /**
     * @param EventDispatcherInterface $dispatcher
     * @return $this
     */
    final public function setEventDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
        $this->dispatcher->addSubscriber($this);

        return $this;
    }

    public static function getSubscribedEvents()
    {
        return [
            ConsoleEvents::COMMAND => 'handleConsoleEvent',
            ConsoleEvents::TERMINATE => 'handleConsoleEvent',
            ConsoleEvents::ERROR => 'handleConsoleEvent',
        ];
    }

    final public function handleConsoleEvent(ConsoleEvent $event)
    {
        $input = $event->getInput();
        if (!$input->hasOption('language')) return;
        if ($input->getOption('language') !== $this->getLanguage()) {
            return;
        }
        if ($event instanceof ConsoleCommandEvent) {
            return $this->onConsoleCommand($event);
        }
        if ($event instanceof ConsoleTerminateEvent) {
            return $this->onConsoleTerminate($event);
        }
        if ($event instanceof ConsoleErrorEvent) {
            return $this->onConsoleError($event);
        }
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
