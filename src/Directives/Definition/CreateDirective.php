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

class CreateDirective extends AbstractDirective implements FieldResolverInterface
{
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public static function name(): string
    {
        return 'create';
    }

    public static function definition(): string
    {
        return <<<'GRAPHQL'
            directive @create(
                target: String,
                type: String
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

            $entity = $serializer->denormalize($arguments, $model);

            $entityManager->persist($entity);
            $entityManager->flush();

            return $serializer->normalize($entity);
        };
    }
}