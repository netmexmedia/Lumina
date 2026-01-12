# @like
The `@like` directive allows you to filter query results using SQL-style
pattern matching. It is useful for partial matches on string fields
(e.g., search by name, email, or title).


## Usage
```graphql
type Query {
    users(name: String @like): [User] @all
}
```
In this example, the `users` query will return all `User` records where
either the `name` or the `role` matches the provided values.

#### Arguments
| Name    | Type   | Description                                 |
| ------- | ------ | ------------------------------------------- |
| column  | String | Optional: override the database column      |
| pattern | String | Required: pattern to match (e.g., `%John%`) |

### Notes
- `@like` must be used on arguments, not on fields directly
- Supports SQL wildcards: `%` for multiple characters, `_` for single character
- Can be used alongside other directives like `@all`, `@orderBy`, `@paginate`

---
[← Previous: @orWhere](or-where.md) | [Next: @in →](in.md)