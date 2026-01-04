<?php

namespace Netmex\Lumina\Context;

use Doctrine\ORM\EntityManagerInterface;
use Netmex\Lumina\ContextBuilderInterface;

readonly class ContextBuilder implements ContextBuilderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function build(): Context
    {
        return new Context(
            user: null,
            entityManager: $this->entityManager,
        );
    }
}