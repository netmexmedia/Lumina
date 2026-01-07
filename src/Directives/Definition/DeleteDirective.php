<?php

namespace Netmex\Lumina\Directives\Definition;

use Doctrine\ORM\QueryBuilder;
use GraphQL\Type\Definition\ResolveInfo;
use Netmex\Lumina\Context\Context;
use Netmex\Lumina\Contracts\ArgumentBuilderDirectiveInterface;
use Netmex\Lumina\Contracts\FieldResolverInterface;
use Netmex\Lumina\Contracts\FieldValueInterface;
use Netmex\Lumina\Directives\AbstractDirective;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class DeleteDirective extends AbstractDirective implements FieldResolverInterface
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

    public function resolveField(FieldValueInterface $value, ?QueryBuilder $queryBuilder): callable
    {
        $model = "App\\Entity\\" . $this->modelClass();
        $normalizer = $this->normalizer;

        return static function (mixed $root, array $arguments, Context $context, ResolveInfo $info) use ($queryBuilder, $model, $normalizer)
        {
            $entityManager = $queryBuilder->getEntityManager();
            $entity = $entityManager->find($model, $arguments['id']);
            $deletedEntity = clone $entity;

            $entityManager->remove($entity);
            $entityManager->flush();

            return $normalizer->normalize($deletedEntity);
        };
    }
}