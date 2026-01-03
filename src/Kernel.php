<?php

namespace Netmex\Lumina;

use GraphQL\GraphQL;
use Netmex\Lumina\Query\QueryIntentBuilder;
use Netmex\Lumina\Schema\SchemaBuilder;

/**
 * @property $executor
 */
readonly class Kernel implements ExecutorInterface
{
    private SchemaBuilderInterface $schemaBuilder;
    private QueryIntentBuilder $intentBuilder;
    private QueryExecutorInterface $queryExecutor;
    private ContextBuilderInterface $contextBuilder;
    private DefaultFieldResolver $defaultFieldResolver;

    public function __construct(SchemaBuilder $schemaBuilder, QueryIntentBuilder $intentBuilder, QueryExecutorInterface $executor, ContextBuilderInterface $contextBuilder, DefaultFieldResolver $defaultFieldResolver) {
        $this->schemaBuilder = $schemaBuilder;
        $this->intentBuilder = $intentBuilder;
        $this->queryExecutor = $executor;
        $this->contextBuilder = $contextBuilder;
        $this->defaultFieldResolver = $defaultFieldResolver;
    }

    public function execute(string $query, array $variables = [], ?string $operation = null): array
    {
        // TODO : Propper error handling
        return GraphQL::executeQuery(
            $this->schemaBuilder->build(),
            $query,
            null,
            $this->contextBuilder->build(),
            $variables,
            $operation,
            function ($root, $args, $context, $info) {
                $intent = $this->intentBuilder->build(
                    $info->parentType->name,
                    $info->fieldName,
                    $args
                );

                $this->queryExecutor->execute($intent);
            }
        )->toArray();
    }
}