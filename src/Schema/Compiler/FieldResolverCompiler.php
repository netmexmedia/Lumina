<?php

declare(strict_types=1);

namespace Netmex\Lumina\Schema\Compiler;

use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Schema;
use Netmex\Lumina\Context\Context;
use Netmex\Lumina\Contracts\SchemaSourceInterface;
use Netmex\Lumina\Execution\DoctrineExecution;

final readonly class FieldResolverCompiler
{
    private SchemaSourceInterface $schemaSource;
    private DoctrineExecution $execution;

    public function __construct(SchemaSourceInterface $schemaSource, DoctrineExecution $doctrineExecution) {
        $this->schemaSource    = $schemaSource;
        $this->execution = $doctrineExecution;
    }

    public function compile(): void
    {
        $schema = $this->schemaSource->schema();
        $this->compileType($schema->getQueryType(), 'Query');
        $this->compileType($schema->getMutationType(), 'Mutation');
    }

    private function compileType(?ObjectType $type, string $typeName): void
    {
        if ($type === null) {
            return;
        }

        foreach ($type->getFields() as $field) {
            $field->resolveFn = $this->makeResolver($typeName, $field);
        }
    }

    private function makeResolver(string $parentType, FieldDefinition $field): callable
    {
        return function (mixed $root, array $arguments, Context $context, ResolveInfo $info) use ($parentType, $field) {
            return $this->execution->executeField(
                $parentType,
                $field,
                $arguments,
                $context,
                $info
            );
        };
    }
}