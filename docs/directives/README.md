# Directives
Lumina uses GraphQL directives to define behavior directly in the schema.
Directives replace traditional resolver logic by describing *what* data
should be fetched rather than *how* it should be fetched.

## Schema-First Approach
In Lumina, the GraphQL schema is the source of truth. Directives attached
to fields and arguments are compiled into an execution intent that is
reused at runtime.

```graphql
type Query {
  users(name: String @where): [User] @all
}
```
In this example:
- The `@all` directive on the `users` field indicates that all user records
  should be fetched.
- The `@where` directive on the `name` argument specifies a filter condition.

## Available Directives
Lumina provides a rich set of directives to handle querying, filtering,
sorting, pagination, relations, mutations, aggregation, authorization,
and more. Below is a checklist of available directives:

### Querying / Filtering
- `@all`
- `@find`
- `@where`
- `@orWhere`
- `@like`
- `@in`
- `@between`

### Sorting / Pagination
- `@orderBy`
- `@limit`
- `@offset`
- `@paginate`

### Relation Handling
- `@hasMany`
- `@belongsTo`
- `@join`

### Mutations / Write Operations
- `@create`
- `@update`
- `@delete`
- `@validate`

### Aggregation / Computed Fields
- `@count`
- `@sum`
- `@avg`
- `@resolver`

### Authorization / Access Control
- `@can`
- `@role`
- `@owner`

### Misc / Utilities
- `@deprecated`

## Creating Custom Directives
You can create custom directives by extending `AbstractDirective` and
implementing the necessary interfaces based on your requirements.
Find more details in the [Creating a Directive](creating-a-directive.md) guide.


---
[← Previous: Getting Started](../getting-started.md) | [Next: @all →](all.md)
