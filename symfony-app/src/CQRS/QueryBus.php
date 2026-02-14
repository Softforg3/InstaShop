<?php

declare(strict_types=1);

namespace App\CQRS;

use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

final class QueryBus
{
    /** @var array<string, QueryHandlerInterface> */
    private array $handlers = [];

    public function __construct(
        #[TaggedIterator('app.query_handler')] iterable $handlers,
    ) {
        foreach ($handlers as $handler) {
            $reflection = new \ReflectionMethod($handler, 'handle');
            $paramType = $reflection->getParameters()[0]->getType()->getName();
            $this->handlers[$paramType] = $handler;
        }
    }

    /**
     * @return mixed
     */
    public function dispatch(object $query)
    {
        $queryClass = get_class($query);

        if (!isset($this->handlers[$queryClass])) {
            throw new \RuntimeException(sprintf('No handler registered for %s', $queryClass));
        }

        return $this->handlers[$queryClass]->handle($query);
    }
}
