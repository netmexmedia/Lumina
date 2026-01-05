<?php

namespace Netmex\Lumina\Execution;

final class ExecutorRegistry
{
    /** @var array<string, QueryExecutorInterface> */
    private array $executors = [];

    public function register(QueryExecutorInterface $executor): void
    {
        $this->executors[$executor->strategy()] = $executor;
    }

    public function forStrategy(string $strategy): QueryExecutorInterface
    {
        if (!isset($this->executors[$strategy])) {
            throw new \RuntimeException(
                "No executor registered for strategy [$strategy]"
            );
        }

        return $this->executors[$strategy];
    }
}