<?php

declare(strict_types=1);

namespace App\CQRS;

use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

final class CommandBus
{
    /** @var array<string, CommandHandlerInterface> */
    private array $handlers = [];

    public function __construct(
        #[TaggedIterator('app.command_handler')] iterable $handlers,
    ) {
        foreach ($handlers as $handler) {
            $this->handlers[$handler::getHandledCommand()] = $handler;
        }
    }

    /**
     * @return mixed
     */
    public function dispatch(object $command)
    {
        $commandClass = get_class($command);

        if (!isset($this->handlers[$commandClass])) {
            throw new RuntimeException(sprintf('No handler registered for %s', $commandClass));
        }

        return $this->handlers[$commandClass]->handle($command);
    }
}
