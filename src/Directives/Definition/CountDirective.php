<?php

namespace Netmex\Lumina\Directives\Definition;

use Doctrine\ORM\QueryBuilder;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\NameNode;
use GraphQL\Language\AST\NodeList;
use GraphQL\Language\AST\NonNullTypeNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Type\Definition\ResolveInfo;
use Netmex\Lumina\Context\Context;
use Netmex\Lumina\Contracts\ArgumentBuilderDirectiveInterface;
use Netmex\Lumina\Contracts\FieldArgumentDirectiveInterface;
use Netmex\Lumina\Contracts\FieldInputDirectiveInterface;
use Netmex\Lumina\Contracts\FieldResolverInterface;
use Netmex\Lumina\Contracts\FieldTypeModifierInterface;
use Netmex\Lumina\Contracts\FieldValueInterface;
use Netmex\Lumina\Directives\AbstractDirective;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class CountDirective extends AbstractDirective implements FieldResolverInterface, FieldTypeModifierInterface
{

    public static function name(): string
    {
        return 'count';
    }

    public static function definition(): string
    {
        return <<<'GRAPHQL'
            directive @count(
                column: String = id
            ) repeatable on FIELD_DEFINITION
        GRAPHQL;
    }

    public function resolveField(FieldValueInterface $value, ?QueryBuilder $queryBuilder): callable
    {
        $sumField = $this->getArgument('column', 'id');

        return static function (mixed $root, array $arguments, Context $context, ResolveInfo $info) use ($queryBuilder, $sumField)
        {
            $alias = current($queryBuilder->getRootAliases());
            $queryBuilder->resetDQLPart('select');

            $result =  $queryBuilder
                ->select("COUNT($alias.$sumField) AS count")
                ->getQuery()
                ->getSingleScalarResult();

            return (int) $result;
        };
    }

    public function modifyFieldType(FieldDefinitionNode $fieldNode, DocumentNode $document): void
    {
        $fieldNode->type = new NonNullTypeNode([
            'type' => new NamedTypeNode([
                'name' => new NameNode(['value' => 'Int']),
            ]),
        ]);
    }
}