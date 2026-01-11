<?php

declare(strict_types=1);

namespace Netmex\Lumina\Schema\Execution;

use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use Netmex\Lumina\Context\Context;
use Netmex\Lumina\Contracts\SchemaSourceInterface;
use Netmex\Lumina\Execution\DoctrineExecution;

final readonly class ExecutionResolver
{
    private SchemaSourceInterface $schemaSource;
    private DoctrineExecution $execution;

    public function __construct(SchemaSourceInterface $schemaSource, DoctrineExecution $execution) {
        $this->schemaSource = $schemaSource;
        $this->execution = $execution;
    }

    public function register(): void
    {
        $schema = $this->schemaSource->getSchema();

        if ($schema === null) {
            throw new \RuntimeException('No schema specified');
        }

        $this->registerTypeResolvers($schema->getQueryType(), 'Query');
        $this->registerTypeResolvers($schema->getMutationType(), 'Mutation');
    }

    private function registerTypeResolvers(?ObjectType $type, string $typeName): void
    {
        if ($type === null) {
            return;
        }

        foreach ($type->getFields() as $field) {
            $field->resolveFn = $this->createFieldResolver($typeName, $field);
        }
    }

    private function createFieldResolver(string $parentType, FieldDefinition $field): callable
    {
        return fn($root, array $args, Context $context, ResolveInfo $info) =>
        $this->execution->executeField($parentType, $field, $args, $context, $info);
    }
}