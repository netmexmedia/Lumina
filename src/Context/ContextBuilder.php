<?php

declare(strict_types=1);

namespace Netmex\Lumina\Context;

use Doctrine\ORM\EntityManagerInterface;
use Netmex\Lumina\Contracts\ContextBuilderInterface;

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