<?php

declare(strict_types=1);

namespace Netmex\Lumina\Directives\Definition;

use Doctrine\ORM\QueryBuilder;
use Netmex\Lumina\Contracts\ArgumentBuilderDirectiveInterface;
use Netmex\Lumina\Directives\AbstractDirective;
use Netmex\Lumina\Validators\ValidatorRegistry;

final class ValidateDirective extends AbstractDirective implements ArgumentBuilderDirectiveInterface
{
    private ValidatorRegistry $registry;

    public function __construct(ValidatorRegistry $registry) {
        $this->registry = $registry;
    }

    public static function name(): string
    {
        return 'validate';
    }

    public static function definition(): string
    {
        return <<<'GRAPHQL'
            directive @validate(
                rules: [String!]!,
            ) repeatable on ARGUMENT_DEFINITION | INPUT_FIELD_DEFINITION
        GRAPHQL;
    }

    public function handleArgumentBuilder(QueryBuilder $queryBuilder, $value): QueryBuilder
    {
        $rules = $this->getArgument('rules', []);

        if (!is_array($rules)) {
            $rules = [$rules];
        }

        foreach ($rules as $identifier) {
            $className = $this->registry->resolve($identifier);
            $validator = new $className($value);

            if (!method_exists($validator, 'handle')) {
                throw new \LogicException("Validator class {$className} must have a handle() method.");
            }

            if (!$validator->handle($value)) {
                throw new \LogicException("Validation failed for rule '{$identifier}'.");
            }
        }

        return $queryBuilder;
    }
}