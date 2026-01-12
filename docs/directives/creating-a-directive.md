# Creating a Custom Directive

Lumina allows you to create your own GraphQL directives to extend
functionality beyond the built-in directives. Custom directives can
modify queries, add arguments, transform the schema AST, or produce
custom runtime output.

---

## Example Directive

Here’s a directive that filters results between a minimum and maximum value
and demonstrates multiple capabilities:

```php
<?php

declare(strict_types=1);

namespace App\GraphQL\Directives;

use Doctrine\ORM\QueryBuilder;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\NameNode;
use GraphQL\Language\AST\NodeList;
use Netmex\Lumina\Contracts\ArgumentBuilderDirectiveInterface;
use Netmex\Lumina\Contracts\FieldArgumentDirectiveInterface;
use Netmex\Lumina\Contracts\FieldInputDirectiveInterface;
use Netmex\Lumina\Contracts\FieldTypeModifierInterface;
use Netmex\Lumina\Contracts\FieldResolverInterface;
use Netmex\Lumina\Directives\AbstractDirective;

final class BetweenDirective extends AbstractDirective implements
    ArgumentBuilderDirectiveInterface,
    FieldInputDirectiveInterface,
    FieldArgumentDirectiveInterface,
    FieldTypeModifierInterface,
    FieldResolverInterface
{
    public static function name(): string
    {
        return 'between';
    }

    public static function definition(): string
    {
        return <<<'GRAPHQL'
            directive @between repeatable on ARGUMENT_DEFINITION
        GRAPHQL;
    }

    // 1️ Create global input type
    public static function inputsDefinition(): string
    {
        return <<<'GRAPHQL'
            input BetweenInput {
                min: Int!
                max: Int!
            }
        GRAPHQL;
    }

    // 2️ Attach argument nodes to a field
    public function argumentNodes(): array
    {
        return [
            new InputValueDefinitionNode([
                'name' => new NameNode(['value' => 'between']),
                'type' => new NamedTypeNode([
                    'name' => new NameNode(['value' => 'BetweenInput']),
                ]),
                'directives' => new NodeList([]),
            ]),
        ];
    }

    // 3️ Modify the Doctrine query
    public function handleArgumentBuilder(QueryBuilder $queryBuilder, $value): QueryBuilder
    {
        if (!is_array($value) || !isset($value['min'], $value['max'])) {
            return $queryBuilder;
        }

        $alias = current($queryBuilder->getRootAliases());
        $column = $this->getColumn();
        $startParam = ':' . $column . '_start';
        $endParam = ':' . $column . '_end';

        $queryBuilder->andWhere("$alias.$column BETWEEN $startParam AND $endParam")
            ->setParameter($startParam, $value['min'])
            ->setParameter($endParam, $value['max']);

        return $queryBuilder;
    }

    // 4️ Modify the field type in the AST (optional)
    public function modifyFieldType(FieldDefinitionNode $fieldNode, DocumentNode $document): void
    {
        // Example: wrap output in a custom type if needed
    }

    // 5️ Transform runtime output (optional)
    public function resolveField($value, ?QueryBuilder $queryBuilder): callable
    {
        return static function ($root, array $args, $context, $info) {
            // Optionally manipulate the result before returning
            return $root;
        };
    }
}
```

And in your schema:
```graphql
type Query {
    users(age: BetweenInput @between): [User] @all
}
```

## Directive Capabilities
| Capability                 | Interface                           | Purpose                                                              |
| -------------------------- | ----------------------------------- | -------------------------------------------------------------------- |
| Modify query execution     | `ArgumentBuilderDirectiveInterface` | Adjust Doctrine queries or fetch logic                               |
| Create global inputs       | `FieldInputDirectiveInterface`      | Add GraphQL input types to the schema                                |
| Attach arguments to fields | `FieldArgumentDirectiveInterface`   | Add field-specific arguments to the AST                              |
| Modify output type         | `FieldTypeModifierInterface`        | Wrap or transform the field’s return type in the schema AST          |
| Transform runtime output   | `FieldResolverInterface`            | Produce or modify the field’s data before returning it to the client |

> A directive can implement any combination of these capabilities depending on what it needs to do.

###  It Works

1. Extend AbstractDirective or implement a Lumina directive interface
2. Define name() and definition() for your directive
3. Optionally implement interfaces to:
   - Modify queries (ArgumentBuilderDirectiveInterface)
   - Add inputs (FieldInputDirectiveInterface)
   - Add field arguments (FieldArgumentDirectiveInterface)
   - Change return type (FieldTypeModifierInterface)
   - Transform runtime data (FieldResolverInterface)
4. Register your directive so Lumina can discover it at runtime
---

### Notes / Best Practices
- Give your directive a unique name
- Keep directive logic lightweight — avoid heavy computation inside
- Implement only the interfaces needed for your use case
- Directives can be repeatable in the schema if needed
- Test your directive with both schema-first examples and runtime queries

---
[← Previous: @deprecated](deprecated.md)

