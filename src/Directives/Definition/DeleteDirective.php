<?php

namespace Netmex\Lumina\Directives\Definition;

use Doctrine\ORM\QueryBuilder;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\NameNode;
use GraphQL\Language\AST\NodeList;
use GraphQL\Type\Definition\ResolveInfo;
use Netmex\Lumina\Context\Context;
use Netmex\Lumina\Contracts\ArgumentBuilderDirectiveInterface;
use Netmex\Lumina\Contracts\FieldArgumentDirectiveInterface;
use Netmex\Lumina\Contracts\FieldResolverInterface;
use Netmex\Lumina\Contracts\FieldValueInterface;
use Netmex\Lumina\Directives\AbstractDirective;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class DeleteDirective extends AbstractDirective implements FieldResolverInterface, FieldArgumentDirectiveInterface
{
    private NormalizerInterface $normalizer;

    public function __construct(SerializerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    public static function name(): string
    {
        return 'delete';
    }

    public static function definition(): string
    {
        return <<<'GRAPHQL'
            directive @delete(
                field: String = id
            ) repeatable on ARGUMENT_DEFINITION | INPUT_FIELD_DEFINITION | FIELD_DEFINITION
        GRAPHQL;
    }

    public function argumentNodes(): array
    {
        return [
            new InputValueDefinitionNode([
                'name' => new NameNode(['value' => 'id']),
                'type' => new NamedTypeNode([
                    'name' => new NameNode(['value' => 'ID'])
                ]),
                'directives' => new NodeList([]),
                'description' => null,
                'defaultValue' => null,
            ])
        ];
    }

    public function resolveField(FieldValueInterface $value, ?QueryBuilder $queryBuilder): callable
    {
        $entityManager = $queryBuilder->getEntityManager();
        $model = $this->resolveEntityFQCN($this->modelClass(), $entityManager);
        $normalizer = $this->normalizer;

        return static function (mixed $root, array $arguments, Context $context, ResolveInfo $info) use ($entityManager, $model, $normalizer)
        {
            $entity = $entityManager->find($model, $arguments['id']);
            $deletedEntity = clone $entity;

            $entityManager->remove($entity);
            $entityManager->flush();

            return $normalizer->normalize($deletedEntity);
        };
    }
}