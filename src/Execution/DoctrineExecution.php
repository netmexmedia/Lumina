<?php

declare(strict_types=1);

namespace Netmex\Lumina\Execution;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\ResolveInfo;
use Netmex\Lumina\Context\Context;
use Netmex\Lumina\Contracts\ExecutionInterface;
use Netmex\Lumina\Intent\Intent;
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

    public function executeField(string $parentTypeName, FieldDefinition $field, array $arguments, Context $context, ResolveInfo $info): array|int|string|float|bool|null
    {
        $intent = $this->getIntent($parentTypeName, $field);

        $queryBuilder = $this->createQueryBuilder($intent->resolver->getModel());

        $this->applyTypeDirectives($intent, $queryBuilder, $arguments);
        $this->applyArgumentDirectives($intent, $queryBuilder, $arguments);

        return $this->executeResolver($intent, $queryBuilder, $arguments, $context, $info);
    }

    private function getIntent(string $parentTypeName, FieldDefinition $field): Intent
    {
        $intent = $this->intentRegistry->get($parentTypeName, $field->name);

        if (!$intent || !$intent->resolver) {
            throw new \RuntimeException("No resolver intent found for {$parentTypeName}.{$field->name}");
        }

        return $intent;
    }

    private function applyTypeDirectives(Intent $intent, QueryBuilder $queryBuilder, array $arguments): void
    {
        foreach ($intent->getTypeModifiers() as $typeName => $typeDirective) {
            foreach ($typeDirective as $directive) {
                $directive->handleArgumentBuilder($queryBuilder, $arguments);
            }
        }
    }

    private function applyArgumentDirectives(Intent $intent, QueryBuilder $queryBuilder, array $arguments): void
    {
        foreach ($intent->modifiers as $argName => $directives) {
            $value = $this->getNestedValue($arguments, $argName);
//            if ($value === null) {
//                continue;
//            }

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

    private function executeResolver(Intent $intent, QueryBuilder $queryBuilder, array $arguments, Context $context, ResolveInfo $info): array|int|string|float|bool|null
    {
        $resolver = $intent->resolver;
        $callable = $resolver->resolveField(new TestFieldValue(), $queryBuilder);
        return $callable(null, $arguments, $context, $info);
    }

    private function createQueryBuilder(string $shortClassName): QueryBuilder
    {
        $fqcn = $this->resolveEntityFQCN($shortClassName);

        if (!$fqcn) {
            throw new \RuntimeException("Cannot find a Doctrine entity with short name '$shortClassName'");
        }

        return $this->entityManager
            ->getRepository($fqcn)
            ->createQueryBuilder('e');
    }

    private function resolveEntityFQCN(string $shortName): ?string
    {
        foreach ($this->entityManager->getMetadataFactory()->getAllMetadata() as $meta) {
            if ($meta->getReflectionClass()->getShortName() === $shortName) {
                return $meta->getName(); // return FQCN
            }
        }

        return null;
    }

//    This is a placeholder for a recursive execution method, not used currently
//    It would be the ideal way to handle nested intents
//    function execute(Intent $intent, ?array $parentRow = null) {
//        $qb = new QueryBuilder($intent->model);
//
//        // Resolver defines base query
//        $intent->resolver->resolveField($qb, $parentRow);
//
//        // Modifiers decorate it
//        foreach ($intent->modifiers as $modifier) {
//            $modifier->handleArgumentBuilder($qb);
//        }
//
//        $result = $intent->resolver->resolveField($qb);
//
//        // Resolve children with NEW query builders
//        foreach ($intent->children as $childIntent) {
//            foreach ($result as &$row) {
//                $row[$childIntent->fieldName] = execute($childIntent, $row);
//            }
//        }
//
//        return $result;
//    }
}
