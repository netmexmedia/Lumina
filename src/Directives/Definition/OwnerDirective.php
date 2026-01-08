<?php

declare(strict_types=1);

namespace Netmex\Lumina\Directives\Definition;

use AllowDynamicProperties;
use Doctrine\ORM\QueryBuilder;
use Netmex\Lumina\Contracts\ArgumentBuilderDirectiveInterface;
use Netmex\Lumina\Directives\AbstractDirective;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class OwnerDirective extends AbstractDirective implements ArgumentBuilderDirectiveInterface
{
    private TokenStorageInterface $tokenStorage;
    private string $userColumn;

    public function __construct(TokenStorageInterface $tokenStorage, String $userColumn) {
        $this->tokenStorage = $tokenStorage;
        $this->userColumn = $userColumn;
    }

    public static function name(): string
    {
        return 'owner';
    }

    public static function definition(): string
    {
        return <<<'GRAPHQL'
            directive @owner repeatable on ARGUMENT_DEFINITION | INPUT_FIELD_DEFINITION | FIELD_DEFINITION| OBJECT
        GRAPHQL;
    }

    public function handleArgumentBuilder(QueryBuilder $queryBuilder, $value): QueryBuilder
    {
        $token = $this->tokenStorage->getToken();
        $user = $token?->getUser();

        if (!$user instanceof UserInterface) {
            throw new \RuntimeException('No authenticated user for @owner');
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $column = $this->userColumn; // TODO: Directive argument for column name as well

        $queryBuilder
            ->andWhere("$rootAlias.$column = :owner_param")
            ->setParameter('owner_param', $user->getId());

        return $queryBuilder;
    }
}