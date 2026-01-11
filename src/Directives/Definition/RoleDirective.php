<?php

declare(strict_types=1);

namespace Netmex\Lumina\Directives\Definition;

use Doctrine\ORM\QueryBuilder;
use Netmex\Lumina\Contracts\ArgumentBuilderDirectiveInterface;
use Netmex\Lumina\Directives\AbstractDirective;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class RoleDirective extends AbstractDirective implements ArgumentBuilderDirectiveInterface
{
    private TokenStorageInterface $tokenStorage;
    private string $userColumn;

    public function __construct(TokenStorageInterface $tokenStorage, String $userColumn) {
        $this->tokenStorage = $tokenStorage;
        $this->userColumn = $userColumn;
    }

    public static function name(): string
    {
        return 'role';
    }

    public static function definition(): string
    {
        return <<<'GRAPHQL'
            directive @role(
                name: String!
            ) repeatable on FIELD_DEFINITION | OBJECT
        GRAPHQL;
    }

    public function handleArgumentBuilder(QueryBuilder $queryBuilder, $value): QueryBuilder
    {
        $token = $this->tokenStorage->getToken();
        $user = $token?->getUser();

        if (!$user instanceof UserInterface) {
            throw new \RuntimeException('No authenticated user for @role');
        }

        if (!in_array($this->getArgument('name'), $user->getRoles(), true)) {
            throw new \RuntimeException(sprintf(
                'User does not have the required role "%s" for this field',
                $this->getArgument('name')
            ));
        }

        return $queryBuilder;
    }
}