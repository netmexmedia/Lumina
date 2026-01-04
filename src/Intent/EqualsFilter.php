<?php

namespace Netmex\Lumina\Intent;

use Doctrine\ORM\QueryBuilder;

final class EqualsFilter implements FilterIntentInterface
{
    public function __construct(
        public string $argument,
        public string $column,
    ) {}

    public function apply(object $builder, array $args): void
    {
        if (!array_key_exists($this->argument, $args)) {
            return;
        }

        if (!$builder instanceof QueryBuilder) {
            throw new \InvalidArgumentException('EqualsFilter requires Doctrine QueryBuilder');
        }

        $param = $this->argument;

        $builder
            ->andWhere(sprintf('e.%s = :%s', $this->column, $param))
            ->setParameter($param, $args[$this->argument]);
    }
}
