<?php

namespace Netmex\Lumina\Context;

use Doctrine\ORM\EntityManagerInterface;

readonly class ContextBuilder implements ContextBuilderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function build(): Context
    {
        return new Context(
            entityManager: $this->entityManager,
        );
    }
}