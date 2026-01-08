<?php

namespace Netmex\Lumina\Directives\Definition;

use Doctrine\ORM\QueryBuilder;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\NameNode;
use GraphQL\Language\AST\NodeList;
use GraphQL\Language\AST\NonNullTypeNode;
use GraphQL\Type\Definition\ResolveInfo;
use Netmex\Lumina\Context\Context;
use Netmex\Lumina\Contracts\ArgumentBuilderDirectiveInterface;
use Netmex\Lumina\Contracts\FieldArgumentDirectiveInterface;
use Netmex\Lumina\Contracts\FieldResolverInterface;
use Netmex\Lumina\Contracts\FieldValueInterface;
use Netmex\Lumina\Directives\AbstractDirective;
use Symfony\Component\Serializer\SerializerInterface;

class UpdateDirective extends AbstractDirective implements FieldResolverInterface, FieldArgumentDirectiveInterface
{
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public static function name(): string
    {
        return 'update';
    }

    public static function definition(): string
    {
        return <<<'GRAPHQL'
            directive @update(
                field: String = id
            ) repeatable on ARGUMENT_DEFINITION | INPUT_FIELD_DEFINITION | FIELD_DEFINITION
        GRAPHQL;
    }

    public function argumentNodes(): array
    {
        return [
            new InputValueDefinitionNode([
                'name' => new NameNode(['value' => 'id']),
                'type' => new NonNullTypeNode([
                    'type' => new NamedTypeNode([
                        'name' => new NameNode(['value' => 'ID']),
                    ]),
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
        $serializer = $this->serializer;

        return static function (mixed $root, array $arguments, Context $context, ResolveInfo $info) use ($entityManager, $model, $serializer)
        {
            $entity = $entityManager->find($model, $arguments['id']);

            $serializer->denormalize($arguments, $model, null, ['object_to_populate' => $entity]);
            $entityManager->flush();

            return $serializer->normalize($entity, null, [
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                },
            ]);
        };
    }
}