<?php

declare(strict_types=1);

namespace Netmex\Lumina\Execution;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\ResolveInfo;
use Netmex\Lumina\Context\Context;
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

    private function applyArgumentDirectives($intent, QueryBuilder $queryBuilder, array $arguments): void
    {
        foreach ($intent->argumentDirectives as $argName => $directives) {
            if (!isset($arguments[$argName])) {
                continue;
            }
            foreach ($directives as $directive) {
                $directive->handleArgumentBuilder($queryBuilder, $arguments[$argName]);
            }
        }
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
