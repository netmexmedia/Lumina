<?php

declare(strict_types=1);

namespace Netmex\Lumina\Execution;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\ResolveInfo;
use Netmex\Lumina\Context\Context;
use Netmex\Lumina\Contracts\ArgumentBuilderDirectiveInterface;
use Netmex\Lumina\Contracts\ExecutionInterface;
use Netmex\Lumina\Intent\Intent;
use Netmex\Lumina\Intent\IntentRegistry;
use Netmex\Lumina\placeholder\TestFieldValue;

class DoctrineExecution implements ExecutionInterface
{
    private EntityManagerInterface $entityManager;
    private IntentRegistry $intentRegistry;

    public function __construct(EntityManagerInterface $entityManager, IntentRegistry $intentRegistry)
    {
        $this->entityManager = $entityManager;
        $this->intentRegistry = $intentRegistry;
    }

    public function executeField(string $parentTypeName, FieldDefinition $field, array $arguments, Context $context, ResolveInfo $info): array {
        $intent = $this->getIntent($parentTypeName, $field);


        dd($this->flattenIntent($intent));
        return $this->executeRecursive($intent, $arguments, $context, $info);
    }

    public function flattenIntent(Intent $intent): array
    {
        $flattened = [];

        // First, flatten children
        foreach ($intent->children as $child) {
            $flattened = array_merge($flattened, $this->flattenIntent($child));
        }

        // Only include the intent itself if it has a resolver
        if ($intent->resolver !== null) {
            $flattened[] = $intent;
        }

        return $flattened;
    }



    private function executeRecursive(Intent $intent, array $arguments, Context $context, ResolveInfo $info, $parentRow = null, ?string $parentModel = null): array
    {
        $model = $intent->resolver?->getModel() ?? $parentModel;

        if (!$model) {
            return [];
        }

        $queryBuilder = $this->createQueryBuilder($model);
        $this->applyModifiers($intent, $queryBuilder, $arguments);

        if (!$intent->resolver) {
            return [];
        }

        $rows = $this->resolveField($intent, $queryBuilder, $parentRow, $arguments, $context, $info);

        foreach ($intent->children as $childIntent) {
            if (!$childIntent->resolver) {
                continue;
            }

            foreach ($rows as &$row) {
                $row[$childIntent->fieldName] = $this->executeRecursive(
                    $childIntent,
                    $arguments,
                    $context,
                    $info,
                    $row,
                    $model
                );
            }
        }

        return $rows;
    }

    private function applyModifiers(Intent $intent, QueryBuilder $qb, array $arguments): void
    {
        foreach ($intent->modifiers as $argName => $directive) {
            if ($directive instanceof ArgumentBuilderDirectiveInterface) {
                $value = $this->getNestedValue($arguments, $argName);
                $directive->handleArgumentBuilder($qb, $value);
            }
        }
    }

    private function resolveField(
        Intent $intent,
        QueryBuilder $qb,
        $parentRow,
        array $arguments,
        Context $context,
        ResolveInfo $info
    ): array {
        $resolverCallable = $intent->resolver->resolveField(new TestFieldValue(), $qb);

        return $resolverCallable($parentRow, $arguments, $context, $info);
    }

    private function getIntent(string $parentTypeName, FieldDefinition $field): Intent
    {
        $intent = $this->intentRegistry->get($parentTypeName, $field->name);

        if (!$intent || !$intent->resolver) {
            throw new \RuntimeException("No resolver intent found for {$parentTypeName}.{$field->name}");
        }

        return $intent;
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
                return $meta->getName();
            }
        }

        return null;
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
}
