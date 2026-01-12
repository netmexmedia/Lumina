# Getting Started

Lumina is a directive-driven GraphQL framework for Symfony. It allows you
to define data access, relations, and behavior directly in your GraphQL
schema using directives instead of resolvers.

## Installation

Install Lumina using Composer:

```bash
composer require netmex/lumina
```

## Configuration
Lumina can be configured in your Symfony application's configuration files.

```yaml
# config/packages/lumina.yaml
lumina:
  endpoint: /graphql

  schema:
    directory: '%kernel.project_dir%/src/graphql'
```

## Defining Your First Schema
Schemas are defined using the GraphQL Schema Definition Language (SDL).
```graphql
type Query {
  users: [User] @all
}

type User {
  id: ID!
  name: String!
}
```

With this schema in place, Lumina will automatically expose a `users`
query without requiring a resolver class.
---
[← Previous: Readme](../README.md) | [Next: Directives Overview →](directives/README.md)
