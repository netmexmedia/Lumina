<?php


namespace Netmex\Lumina\Context;

use Doctrine\ORM\EntityManagerInterface;

readonly class Context
{
    public readonly ?object $user;
    public readonly EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
}