<?php

declare(strict_types=1);

namespace Netmex\Lumina\Directives\Definition;

use Doctrine\ORM\QueryBuilder;
use Netmex\Lumina\Contracts\ArgumentBuilderDirectiveInterface;
use Netmex\Lumina\Directives\AbstractDirective;
use Netmex\Lumina\Permissions\PermissionRegistry;

final class CanDirective extends AbstractDirective implements ArgumentBuilderDirectiveInterface
{
    private PermissionRegistry $registry;

    public function __construct(PermissionRegistry $registry) {
        $this->registry = $registry;
    }

    public static function name(): string
    {
        return 'can';
    }

    public static function definition(): string
    {
        return <<<'GRAPHQL'
            directive @can(
                permission: [String!]!,
            ) repeatable on FIELD_DEFINITION | OBJECT
        GRAPHQL;
    }

    public function handleArgumentBuilder(QueryBuilder $queryBuilder, $value): QueryBuilder
    {
        $identifiers = $this->getArgument('permission', []);

        if (!is_array($identifiers)) {
            $identifiers = [$identifiers];
        }

        foreach ($identifiers as $identifier) {
            $className = $this->registry->resolve($identifier);
            $instance = new $className();

            if (!method_exists($instance, 'handle')) {
                throw new \LogicException("Permission class {$className} must have a handle() method.");
            }

            if (!$instance->handle()) {
                throw new \LogicException("Permission denied by '{$identifier}'.");
            }
        }

        return $queryBuilder;
    }
}