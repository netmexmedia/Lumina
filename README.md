# Netmex Lumina


[![Packagist Version](https://img.shields.io/packagist/v/netmex/lumina?color=blue&label=packagist)](https://packagist.org/packages/netmex/lumina)
[![PHP Version](https://img.shields.io/packagist/php-v/netmex/lumina?color=brightgreen)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

**LuminaBundle** — A Symfony bundle providing Lighthouse-inspired GraphQL support with automatic Doctrine integration, custom resolvers, scalars, directives, queries, and mutations.

## About
Netmex Lumina is a **directive-driven GraphQL framework for Symfony**.

Instead of writing resolvers, repositories, or query builders by hand, Lumina lets you describe behavior directly in your GraphQL schema using custom directives. Those directives are compiled once into Intents, which are then executed efficiently at runtime.

Lumina focuses on:
* Clean architecture
* Zero boilerplate resolvers
* Strong separation between schema, intent, and execution
* First-class Symfony & Doctrine integration

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

---
### 2. Directives = Behavior
Each directive represents a unit of intent.

Examples:
* `@all` - Fetch all records.
* `@where` - Filter records by field.
* `@orderBy` - Sort records.

Directives are:
* Reusable
* Composable
* Framework-agnostic at the schema level

### 3. Intent Compilation
At schema compile time:
1. The GraphQL AST is traversed once
2. Directives are instantiated
3. Intents are built per Type.Field

These intents are stored in an IntentRegistry and reused during execution.
* No runtime AST traversal
* No resolver discovery
* No reflection hacks

### 4. Execution via Doctrine
At runtime:
* Lumina resolves a field
* Looks up its compiled intent
* Builds a Doctrine `QueryBuilder`
* Applies argument directives
* Executes the resolver directive

## Installation

```bash
composer require netmex/lumina
```
---

### Using Directives
Lumina comes with many **built-in directives**, including:
- Query directives: `@all`, `@find`, `@where`, `@orderBy`, `@limit`, `@offset`, `@paginate`
- Relation directives: `@hasMany`, `@belongsTo`, `@join`
- Mutation directives: `@create`, `@update`, `@delete`, `@validate`
- Aggregation directives: `@count`, `@sum`, `@avg`
- Access control directives: `@can`, `@role`, `@owner`
- Utilities: `@deprecated`

For detailed usage and examples, see the [Directive Documentation](docs/directives/README.md).

---

## Extending Lumina

### Creating a Directive
Lumina allows you to create custom directives that can:

1. Modify query execution
2. Create input types
3. Attach arguments to fields
4. Modify the field’s output type
5. Transform runtime output data

See the Creating a [Directive Guide](docs/directives/creating-a-directive.md) for a full example.
## Why Lumina?
1. No resolvers
2. No controller logic
3. No duplicated query code
4. One AST pass
5. Explicit intent
6. Symfony & Doctrine native

### Lumina is ideal if you want:
* Schema-first GraphQL
* Strong consistency
* Minimal boilerplate
* High performance

## Project Status
#### Actively developed
The core architecture is stable and functional. More directives and documentation are planned.

## License
MIT License
