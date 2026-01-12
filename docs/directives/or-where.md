# @orWhere
The `@orWhere` directive allows you to apply OR-based filtering on query results.
It works like `@where`, but multiple conditions are combined using OR logic
instead of AND. This is useful when you want to match any of several criteria.

## Usage
```graphql
type Query {
    users(name: String @orWhere, role: String @orWhere): [User] @all
}
```
In this example, the `users` query will return all `User` records where
either the `name` or the `role` matches the provided values.

#### Arguments
| Name     | Type   | Description                                      |
| -------- | ------ | ------------------------------------------------ |
| column   | String | Optional: override the database column           |
| operator | String | Optional: comparison operator (e.g. `=`, `LIKE`) |


### Notes
- `@orWhere` must be used on arguments, not on fields directly
- Multiple `@orWhere` arguments are combined with OR logic
- Can be used alongside other directives like `@all`, `@orderBy`, `@paginate`

---
[← Previous: @where](where.md) | [Next: @like →](like.md)