<?php

namespace Netmex\Lumina;

use GraphQL\GraphQL;
use Netmex\Lumina\Schema\SchemaBuilder;

readonly class Kernel implements ExecutorInterface
{
    private SchemaBuilderInterface $schemaBuilder;
    private ContextBuilderInterface $contextBuilder;
    private DefaultFieldResolver $defaultFieldResolver;

    public function __construct(SchemaBuilder $schemaBuilder, ContextBuilderInterface $contextBuilder, DefaultFieldResolver $defaultFieldResolver) {
        $this->schemaBuilder = $schemaBuilder;
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
            $this->defaultFieldResolver
        )->toArray();
    }
}