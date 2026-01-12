# @where
The `@where` directive allows you to filter query results based on field arguments.
It is commonly used in combination with directives like `@all` or `@find`
to restrict the data returned by a query.

## Usage
```graphql
type Query {
    users(name: String @where): [User] @all
}
```
In this example, the `users` query can be filtered by the `name` argument
using the` @where` directive.

#### Arguments
| Name     | Type   | Description                                      |
| -------- | ------ | ------------------------------------------------ |
| column   | String | Optional: override the database column           |
| operator | String | Optional: comparison operator (e.g. `=`, `LIKE`) |


### Notes
- `@where` must be used on arguments, not on fields directly
- Works best in combination with query directives like `@all` or `@find`
- Supports basic operators (`=,` `!=`, `<`, `>`, `LIKE`, etc.)

---
[← Previous: @find](find.md) | [Next: @orWhere →](or-where.md)