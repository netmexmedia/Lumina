<?php

namespace Netmex\Lumina\Schema\Compiler;

use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Schema;
use Netmex\Lumina\Context\Context;
use Netmex\Lumina\Execution\DoctrineExecution;

final readonly class FieldResolverCompiler
{
    private DoctrineExecution $execution;

    public function __construct(DoctrineExecution $doctrineExecution) {
        $this->execution = $doctrineExecution;
    }

    public function compile(Schema $schema): void
    {
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