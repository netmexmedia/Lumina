<?php

declare(strict_types=1);

namespace Netmex\Lumina\Execution;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\ResolveInfo;
use Netmex\Lumina\Context\Context;
use Netmex\Lumina\Contracts\ExecutionInterface;
use Netmex\Lumina\Intent\IntentRegistry;
use Netmex\Lumina\placeholder\TestFieldValue;

// TODO: In the future we need to account for subscriptions, This requires a new executionMethod
class DoctrineExecution implements ExecutionInterface
{
    private EntityManagerInterface $entityManager;
    private IntentRegistry $intentRegistry;

    public function __construct(EntityManagerInterface $entityManager, IntentRegistry $intentRegistry) {
        $this->entityManager = $entityManager;
        $this->intentRegistry = $intentRegistry;
    }

    public function executeField(string $parentTypeName, FieldDefinition $field, array $arguments, Context $context, ResolveInfo $info): array
    {
        $intent = $this->getIntent($parentTypeName, $field);
        $queryBuilder = $this->createQueryBuilder($intent->resolverDirective->modelClass());

        $this->applyTypeDirectives($intent, $queryBuilder, $arguments);
        $this->applyArgumentDirectives($intent, $queryBuilder, $arguments);

        return $this->executeResolver($intent, $queryBuilder, $arguments, $context, $info);
    }

    private function getIntent(string $parentTypeName, FieldDefinition $field)
    {
        $intent = $this->intentRegistry->get($parentTypeName, $field->name);

        if (!$intent || !$intent->resolverDirective) {
            throw new \RuntimeException("No resolver intent found for {$parentTypeName}.{$field->name}");
        }

        return $intent;
    }

    private function applyTypeDirectives($intent, QueryBuilder $queryBuilder, array $arguments): void
    {
        foreach ($intent->getTypeDirectives() as $typeName => $typeDirective) {
            foreach ($typeDirective as $directive) {
                $directive->handleArgumentBuilder($queryBuilder, $arguments);
            }
        }
    }

    private function applyArgumentDirectives($intent, QueryBuilder $queryBuilder, array $arguments): void
    {
        foreach ($intent->argumentDirectives as $argName => $directives) {
            $value = $this->getNestedValue($arguments, $argName);
            if ($value === null) {
                continue;
            }

            foreach ($directives as $directive) {
                $directive->handleArgumentBuilder($queryBuilder, $value);
            }
        }
    }

    private function getNestedValue(array $args, string $path): mixed
    {
        $keys = explode('.', $path);
        $value = $args;
        foreach ($keys as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                return null;
            }
            $value = $value[$key];
        }
        return $value;
    }

    private function executeResolver($intent, QueryBuilder $queryBuilder, array $arguments, Context $context, ResolveInfo $info): array
    {
        $resolver = $intent->resolverDirective;
        $callable = $resolver->resolveField(new TestFieldValue(), $queryBuilder);
        return $callable(null, $arguments, $context, $info);
    }

    private function createQueryBuilder(string $model): QueryBuilder
    {
        return $this->entityManager
            ->getRepository('App\\Entity\\' . $model)
            ->createQueryBuilder('e');
    }
}
