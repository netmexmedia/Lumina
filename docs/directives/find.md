# @find
The `@find` directive fetches a single record by its primary key (usually `id`).
It is commonly used for read operations where you need a specific entity instead
of a list.

## Usage
```graphql
type Query {
  user(id: ID!): User @find
}
```
In this example, the `user` field will return the `User` record with the specified `id` without requiring a resolver.

#### Arguments
The `@find` directive does not accept additional arguments itself; the
primary key argument is provided through the field definition.

### Notes
- `@find` is intended for single-record queries
- The field must specify the primary key argument (commonly `id`)
- Filtering and additional logic can be applied using `@where` if needed

---
[← Previous: @all](all.md) | [Next: @where →](where.md)