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

    public function executeField(
        string $parentTypeName,
        FieldDefinition $field,
        array $arguments,
        Context $context,
        ResolveInfo $info
    ): array {
        $intent = $this->getIntent($parentTypeName, $field);
        $result = $this->executeRecursive($intent, $arguments, $context, $info);

        return $result;
    }

    private function executeRecursive(
        Intent $intent,
        array $arguments,
        Context $context,
        ResolveInfo $info,
        $parentRow = null,
        ?string $parentModel = null
    ): array {
        $model = $intent->resolver?->getModel() ?? $parentModel;
        if (!$model) return [];

        // 1️⃣ Build QueryBuilder for this intent
        $qb = $this->createQueryBuilder($model, 'root');

        // 2️⃣ Apply child constraints (hasMany, etc.)
        $this->applyChildConstraints($intent, $qb);

        // 3️⃣ Apply argument modifiers for this intent
        $this->applyModifiers($intent, $qb, $arguments);

        if (!$intent->resolver) return [];

        // 4️⃣ Fetch rows for this intent
        $rows = $this->resolveField($intent, $qb, $parentRow, $arguments, $context, $info);

        // 5️⃣ Recursively fetch children
        foreach ($intent->children as $childIntent) {
            if (!$childIntent->resolver) continue;

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

    // =======================
    // Child Constraints
    // =======================
    private function applyChildConstraints(Intent $intent, QueryBuilder $qb): void
    {
        $i = 0;

        foreach ($intent->children as $childIntent) {
            $meta = $childIntent->metaData;

            if (!$childIntent->resolver || !$meta || $meta->getStrategy() !== 'hasMany') {
                continue;
            }

            $childModel = $meta->getModel();
            if (!$childModel) continue;

            $childAlias = 'c' . (++$i);

            $subQb = $this->buildChildSubQuery($intent, $childIntent, $childAlias);

            $qb->andWhere(
                $qb->expr()->exists($subQb->getDQL())
            );
        }
    }

    private function buildChildSubQuery(Intent $parentIntent, Intent $childIntent, string $childAlias): QueryBuilder
    {
        $subQb = $this->createQueryBuilder($childIntent->metaData->getModel(), $childAlias);

        // Determine association field on child that references parent
        $associationField = $this->getAssociationField($parentIntent, $childIntent);

        $subQb->select('1')
            ->andWhere(sprintf('%s.%s = root', $childAlias, $associationField));

        $this->applyModifiers($childIntent, $subQb, []);

        return $subQb;
    }

    private function getAssociationField(Intent $parentIntent, Intent $childIntent): string
    {
        // This should match the property on the child entity that references the parent
        return strtolower($parentIntent->getMetaData()->getModel());
    }

    // =======================
    // Modifiers
    // =======================
    private function applyModifiers(Intent $intent, QueryBuilder $qb, array $arguments): void
    {
        foreach ($intent->modifiers as $argName => $directive) {
            if ($directive instanceof ArgumentBuilderDirectiveInterface) {
                $value = $this->getNestedValue($arguments, $argName);
                $directive->handleArgumentBuilder($qb, $value);
            }
        }
    }

    // =======================
    // Resolve fields
    // =======================
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

    // =======================
    // Helpers
    // =======================
    private function getIntent(string $parentTypeName, FieldDefinition $field): Intent
    {
        $intent = $this->intentRegistry->get($parentTypeName, $field->name);

        if (!$intent || !$intent->resolver) {
            throw new \RuntimeException("No resolver intent found for {$parentTypeName}.{$field->name}");
        }

        return $intent;
    }

    private function createQueryBuilder(string $shortClassName, string $alias): QueryBuilder
    {
        $fqcn = $this->resolveEntityFQCN($shortClassName);

        if (!$fqcn) {
            throw new \RuntimeException("Cannot find a Doctrine entity with short name '$shortClassName'");
        }

        return $this->entityManager
            ->getRepository($fqcn)
            ->createQueryBuilder($alias);
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
            if (!is_array($value) || !array_key_exists($key, $value)) return null;
            $value = $value[$key];
        }

        return $value;
    }
}
