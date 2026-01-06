# Netmex Lumina

## About
Netmex Lumina is a directive-driven GraphQL framework for Symfony.

Instead of writing resolvers, repositories, and query builders by hand, Lumina lets you describe behavior directly in your GraphQL schema using custom directives.
Those directives are compiled once into Intents, which are then executed efficiently at runtime.

Lumina focuses on:
* clean architecture.
* zero boilerplate resolvers.
* strong separation between schema, intent, and execution.
* first-class Symfony & Doctrine integration.

## Core Concepts

### 1. Schema-Driven Behavior
Your GraphQL schema is the source of truth.

```graphql
type Query {
  users(name: String @where): [User] @all
}
```
No resolver classes.

No wiring.

The schema defines everything.

### 2. Directives = Behavior
Each directive represents a unit of intent.

Examples:
* `@all` - Fetch all records.
* `@where` - Filter records by field.
* `@orderBy` - Sort records.

Directives are:
* reusable
* composable
* framework-agnostic at the schema level

### 3. Intent Compilation
At schema compile time:
* the GraphQL AST is traversed once
* directives are instantiated
* intents are built per Type.Field

These intents are stored in an IntentRegistry and reused during execution.
* No runtime AST traversal
* No resolver discovery
* No reflection hacks

### 4. Execution via Doctrine
At runtime:
* Lumina resolves a field
* Looks up its compiled intent
* Builds a Doctrine QueryBuilder
* Applies argument directives
* Executes the resolver directive

## Installation

```bash
composer require netmex/lumina
```

## Creating a Directive
##### Field Resolver Directive
```php
<?php

namespace Netmex\Lumina\Directives\Definition;

use Doctrine\ORM\QueryBuilder;
use GraphQL\Type\Definition\ResolveInfo;
use Netmex\Lumina\Context\Context;
use Netmex\Lumina\Contracts\FieldResolverInterface;
use Netmex\Lumina\Contracts\FieldValueInterface;
use Netmex\Lumina\Directives\AbstractDirective;

final class AllDirective extends AbstractDirective implements FieldResolverInterface
{
    public static function name(): string
    {
        return 'all';
    }

    public static function definition(): string
    {
        return <<<'GRAPHQL'
        directive @all(
            model: String
        ) on FIELD_DEFINITION
        GRAPHQL;
    }

    public function resolveField(
        FieldValueInterface $value,
        ?QueryBuilder $queryBuilder
    ): callable {
        return static function (
            mixed $root,
            array $arguments,
            Context $context,
            ResolveInfo $info
        ) use ($queryBuilder) {
            return $queryBuilder->getQuery()->getArrayResult();
        };
    }
}
```

##### Argument Directive
```php
<?php

namespace Netmex\Lumina\Directives\Definition;

use Doctrine\ORM\QueryBuilder;
use Netmex\Lumina\Contracts\ArgumentBuilderDirectiveInterface;
use Netmex\Lumina\Directives\AbstractDirective;

final class WhereDirective extends AbstractDirective implements ArgumentBuilderDirectiveInterface
{
    public static function name(): string
    {
        return 'where';
    }

    public static function definition(): string
    {
        return <<<'GRAPHQL'
        directive @where(
            on: String
        ) repeatable on ARGUMENT_DEFINITION
        GRAPHQL;
    }

    public function handleArgumentBuilder(
        QueryBuilder $queryBuilder,
        $value
    ): QueryBuilder {
        $column = $this->nodeName();
        $param  = ':' . $column;

        $queryBuilder
            ->andWhere("e.$column = $param")
            ->setParameter($param, $value);

        return $queryBuilder;
    }
}
```

## Why Lumina?
1. No resolvers
2. No controller logic
3. No duplicated query code
4. One AST pass
5. Explicit intent
6. Symfony & Doctrine native

Lumina is ideal if you want:
* schema-first GraphQL
* strong consistency
* minimal boilerplate
* high performance execution