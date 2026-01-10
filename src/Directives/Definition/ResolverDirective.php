<?php

namespace Netmex\Lumina\Directives\Definition;

use Doctrine\ORM\QueryBuilder;
use GraphQL\Type\Definition\ResolveInfo;
use Netmex\Lumina\Context\Context;
use Netmex\Lumina\Contracts\FieldResolverInterface;
use Netmex\Lumina\Contracts\FieldValueInterface;
use Netmex\Lumina\Directives\AbstractDirective;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class ResolverDirective extends AbstractDirective implements FieldResolverInterface
{

    private ContainerInterface $container;
    private SerializerInterface $serializer;

    public function __construct(ContainerInterface $container, SerializerInterface $serializer)
    {
        $this->container = $container;
        $this->serializer = $serializer;
    }

    public static function name(): string
    {
        return 'resolver';
    }

    public static function definition(): string
    {
        return <<<'GRAPHQL'
            directive @resolver(
                class: String!
            ) repeatable on ARGUMENT_DEFINITION | INPUT_FIELD_DEFINITION | FIELD_DEFINITION
        GRAPHQL;
    }

    public function resolveField(FieldValueInterface $value, ?QueryBuilder $queryBuilder): callable
    {
        $classArg = $this->getArgument('class');

        $method = null;
        if (str_contains($classArg, '::')) {
            [$shortName, $method] = explode('::', $classArg, 2);
        } else {
            $shortName = $classArg;
        }

        $resolverClass = $this->getResolver($shortName);

        if (!$resolverClass) {
            throw new \RuntimeException("Resolver not found: {$shortName}");
        }

        return function (mixed $root, array $arguments, Context $context, ResolveInfo $info) use ($resolverClass, $method) {

            // Get resolver from container (DI-safe)
            $resolver = $this->container->get($resolverClass);

            // Resolve callable
            if ($method !== null) {
                if (!method_exists($resolver, $method)) {
                    throw new \RuntimeException("Method {$method} does not exist on resolver {$resolverClass}");
                }
                $callable = [$resolver, $method];
            } elseif (is_callable($resolver)) {
                $callable = $resolver;
            } else {
                throw new \RuntimeException(
                    "Resolver {$resolverClass} must be callable or specify a method"
                );
            }

            // Execute resolver
            $result = $callable($root, $arguments, $context, $info);

            // Serializer context (safe circular handling)
            $normalizeContext = [
                AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => static function ($object) {
                    return method_exists($object, 'getId') ? $object->getId() : null;
                },
            ];

            // Traversable → array
            if ($result instanceof \Traversable) {
                $result = iterator_to_array($result);
            }

            // Array result
            if (is_array($result)) {
                return array_map(
                    fn ($item) => is_object($item)
                        ? $this->serializer->normalize($item, null, $normalizeContext)
                        : $item,
                    $result
                );
            }

            // Single object
            if (is_object($result)) {
                return $this->serializer->normalize($result, null, $normalizeContext);
            }

            // Scalar
            return $result;
        };
    }

}