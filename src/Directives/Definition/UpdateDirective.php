<?php

namespace Netmex\Lumina\Directives\Definition;

use Doctrine\ORM\QueryBuilder;
use GraphQL\Type\Definition\ResolveInfo;
use Netmex\Lumina\Context\Context;
use Netmex\Lumina\Contracts\ArgumentBuilderDirectiveInterface;
use Netmex\Lumina\Contracts\FieldResolverInterface;
use Netmex\Lumina\Contracts\FieldValueInterface;
use Netmex\Lumina\Directives\AbstractDirective;
use Symfony\Component\Serializer\SerializerInterface;

class UpdateDirective extends AbstractDirective implements FieldResolverInterface
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

    public function resolveField(FieldValueInterface $value, ?QueryBuilder $queryBuilder): callable
    {
        $model = "App\\Entity\\" . $this->modelClass();
        $serializer = $this->serializer;

        return static function (mixed $root, array $arguments, Context $context, ResolveInfo $info) use ($queryBuilder, $model, $serializer)
        {
            $entityManager = $queryBuilder->getEntityManager();
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