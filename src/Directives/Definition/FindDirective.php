<?php

namespace Netmex\Lumina\Directives\Definition;

use Doctrine\ORM\QueryBuilder;
use GraphQL\Type\Definition\ResolveInfo;
use Netmex\Lumina\Context\Context;
use Netmex\Lumina\Directives\AbstractDirective;
use Netmex\Lumina\Directives\FieldResolverInterface;
use Netmex\Lumina\Directives\FieldValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class FindDirective extends AbstractDirective implements FieldResolverInterface
{
    private NormalizerInterface $normalizer;

    public function __construct(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    public static function name(): string
    {
        return 'find';
    }

    public static function definition(): string
    {
        return <<<'GRAPHQL'
            directive @find(
                model: String,
                resolver: String
            ) on FIELD_DEFINITION
        GRAPHQL;
    }

    public function resolveField(FieldValueInterface $value, ?QueryBuilder $queryBuilder): callable
    {
        $normalizer = $this->normalizer;

        return static function (mixed $root, array $args, Context $context, ResolveInfo $info) use ($queryBuilder, $normalizer)
        {
            $result = $queryBuilder->getQuery()->getSingleResult();

            return $normalizer->normalize($result);
        };
    }
}
