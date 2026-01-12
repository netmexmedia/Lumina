# @all
The `@all` directive fetches all records for a given type. It is the most
commonly used query directive and typically serves as the entry point
for read operations.

## Usage
```graphql
type Query {
  users: [User] @all
}
```
In this example, the users field will return all User records without
requiring a resolver.

#### Arguments
The `@all` directive does not accept any arguments.

### Notes
- `@all` is intended for read-only query operations
- It should be applied to fields that return lists
- Most filtering and transformation logic is provided by companion
  directives such as `@where` and `@orderBy`
---
[← Previous: Directives Overview](README.md) | [Next: @find →](find.md)