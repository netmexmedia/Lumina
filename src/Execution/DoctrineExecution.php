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

    public function executeField(string $parentTypeName, FieldDefinition $field, array $arguments, Context $context, ResolveInfo $info)
    {
        $intent = $this->getIntent($parentTypeName, $field);

        // Fully recursive execution
        $result = $this->executeRecursive($intent, $arguments, $context, $info);

        return $result;
    }

    private function executeRecursive(Intent $intent, array $arguments, Context $context, ResolveInfo $info, $parentRow = null): array
    {
        // 1️⃣ Create QueryBuilder only if we have a resolver
        $queryBuilder = null;
        if ($intent->resolver) {
            $queryBuilder = $this->createQueryBuilder($intent->resolver->getModel());
        }

        // 2️⃣ Apply modifiers for this Intent (always!)
        foreach ($intent->modifiers as $argName => $directive) {
            $value = $this->getNestedValue($arguments, $argName);

            if ($directive instanceof ArgumentBuilderDirectiveInterface) {
                // If no resolver yet, we still apply to the parent's QueryBuilder
                if ($queryBuilder) {
                    $directive->handleArgumentBuilder($queryBuilder, $value);
                }
            }
        }

        // 3️⃣ Call resolver if it exists
        $rows = [];
        if ($intent->resolver) {
            $resolverCallable = $intent->resolver->resolveField(new TestFieldValue(), $queryBuilder);
            $rows = $resolverCallable($parentRow, $arguments, $context, $info);
        }

        // 4️⃣ Recursively execute children (always), passing parentRow
        foreach ($intent->children as $childIntent) {
            if (!is_array($rows)) continue;

            foreach ($rows as &$row) {
                $childResult = $this->executeRecursive(
                    $childIntent,
                    $arguments,
                    $context,
                    $info,
                    $row
                );

                // Only attach if there is actual data
                if ($childIntent->resolver && !empty($childResult)) {
                    $row[$childIntent->fieldName] = $childResult;
                }
            }
        }


        return $rows;
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
