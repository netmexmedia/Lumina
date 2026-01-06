<?php

namespace Netmex\Lumina\Schema\Compiler;

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
        $queryType = $schema->getQueryType();
        if ($queryType === null) {
            throw new \RuntimeException(
                'GraphQL schema must define a Query root type.'
            );
        }

        foreach ($queryType->getFields() as $field) {
            $field->resolveFn = $this->makeResolver($field);
        }
    }

    private function makeResolver($field): callable
    {
        return function (mixed $root, array $arguments, Context $context, ResolveInfo $info ) use ($field)
        {
            return $this->execution->executeField(
                $field,
                $arguments,
                $context,
                $info
            );
        };
    }
}