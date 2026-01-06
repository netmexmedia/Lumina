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

// TODO need to figure out how i can avoid running through the AST here again
class DoctrineExecution implements ExecutionInterface
{
    private EntityManagerInterface $entityManager;
    private IntentRegistry $intentRegistry;

    public function __construct(EntityManagerInterface $entityManager, IntentRegistry $intentRegistry) {
        $this->entityManager = $entityManager;
        $this->intentRegistry = $intentRegistry;
    }

    public function executeField(FieldDefinition $field, array $arguments, Context $context, ResolveInfo $info): array {

        $resolverDirective = $this->findResolverDirective($field);
        $queryBuilder = $this->createQueryBuilder($resolverDirective->modelClass());

        $this->applyArgumentDirectives(
            $field,
            $queryBuilder,
            $arguments
        );

        return $this->executeResolverDirective($resolverDirective, $queryBuilder, $arguments, $context, $info);
    }

    private function createQueryBuilder(string $model): QueryBuilder
    {
        return $this->entityManager
            ->getRepository('App\\Entity\\'. $model)
            ->createQueryBuilder('e');
    }

    private function findResolverDirective(FieldDefinition $field): FieldResolverInterface
    {
        foreach ($field->astNode->directives as $directiveNode) {


            $directives = $this->intentRegistry->get($directiveNode->name->value);

            foreach ($directives as $directive) {
                if ($directive instanceof FieldResolverInterface) {
                    return $directive;
                }
            }
        }

        throw new \RuntimeException(
            "No resolver directive found for field {$field->name}"
        );
    }

    private function applyArgumentDirectives(FieldDefinition $field, QueryBuilder $queryBuilder, array $arguments): void {
        foreach ($field->astNode->arguments as $argumentNode) {
            $argName = $argumentNode->name->value;

            if (!array_key_exists($argName, $arguments)) {
                continue;
            }

            foreach ($argumentNode->directives as $directiveNode) {
                $directives = $this->intentRegistry->get($directiveNode->name->value);

                foreach ($directives as $directive) {
                    if ($directive instanceof ArgumentBuilderDirectiveInterface) {
                        $directive->handleArgumentBuilder(
                            $queryBuilder,
                            $arguments[$argName]
                        );
                    }
                }
            }
        }
    }

    private function executeResolverDirective(FieldResolverInterface $resolverDirective, QueryBuilder $queryBuilder, array $arguments, Context $context, ResolveInfo $info): array {
        $callable = $resolverDirective->resolveField(
            new TestFieldValue(),
            $queryBuilder
        );

        return $callable(null, $arguments, $context, $info);
    }
}