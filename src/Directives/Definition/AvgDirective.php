<?php

namespace Netmex\Lumina\Directives\Definition;

use Doctrine\ORM\QueryBuilder;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\NameNode;
use GraphQL\Language\AST\NonNullTypeNode;
use GraphQL\Type\Definition\ResolveInfo;
use Netmex\Lumina\Context\Context;
use Netmex\Lumina\Contracts\FieldResolverInterface;
use Netmex\Lumina\Contracts\FieldTypeModifierInterface;
use Netmex\Lumina\Contracts\FieldValueInterface;
use Netmex\Lumina\Directives\AbstractDirective;

class AvgDirective extends AbstractDirective implements FieldResolverInterface, FieldTypeModifierInterface
{
    public static function name(): string
    {
        return 'avg';
    }

    public static function definition(): string
    {
        return <<<'GRAPHQL'
            directive @avg(
                column: String = id
            ) repeatable on ARGUMENT_DEFINITION | INPUT_FIELD_DEFINITION | FIELD_DEFINITION
        GRAPHQL;
    }

    public function resolveField(FieldValueInterface $value, ?QueryBuilder $queryBuilder): callable
    {
        $avgField = $this->getArgument('column', 'id');

        return static function (mixed $root, array $arguments, Context $context, ResolveInfo $info) use ($queryBuilder, $avgField)
        {
            $alias = current($queryBuilder->getRootAliases());

            $queryBuilder->resetDQLPart('select');


            $result = $queryBuilder
                ->select("AVG($alias.$avgField) AS avgResult")
                ->getQuery()
                ->getSingleScalarResult();

            return (float) $result;
        };
    }

    public function modifyFieldType(FieldDefinitionNode $fieldNode, DocumentNode $document): void
    {
        $fieldNode->type = new NonNullTypeNode([
            'type' => new NamedTypeNode([
                'name' => new NameNode(['value' => 'Float']),
            ]),
        ]);
    }
}