<?php

namespace Netmex\Lumina\Execution;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\ResolveInfo;
use Netmex\Lumina\Context\Context;
use Netmex\Lumina\Contracts\ArgumentBuilderDirectiveInterface;
use Netmex\Lumina\Contracts\FieldResolverInterface;
use Netmex\Lumina\Intent\IntentRegistry;
use Netmex\Lumina\placeholder\TestFieldValue;

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
        $intent = $this->intentRegistry->get($parentTypeName, $field->name);

        if (!$intent || !$intent->resolverDirective) {
            throw new \RuntimeException("No resolver intent found for {$parentTypeName}.{$field->name}");
        }

        $queryBuilder = $this->createQueryBuilder($intent->resolverDirective->modelClass());

        foreach ($intent->argumentDirectives as $argName => $directives) {
            if (!isset($arguments[$argName])) {
                continue;
            }
            foreach ($directives as $directive) {
                $directive->handleArgumentBuilder($queryBuilder, $arguments[$argName]);
            }
        }

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
