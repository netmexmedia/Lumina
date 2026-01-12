# @in
The `@in` directive allows you to filter query results by checking if a
field’s value exists within a provided list of values. It is useful
for matching multiple possibilities at once.

## Usage
```graphql
type Query {
    users(role: [String!] @in): [User] @all
}
```
In this example, the `users` query will return all `User` records where
the `role` matches any of the values in the provided array.

#### Arguments
| Name   | Type   | Description                                |
| ------ | ------ | ------------------------------------------ |
| column | String | Optional: override the database column     |
| values | [Any!] | Required: array of values to match against |

### Notes
- `@in` must be used on arguments, not on fields directly
- Supports scalar types (strings, integers, etc.)
- Can be used alongside other directives like `@all`, `@orderBy`, `@paginate`

---
[← Previous: @like](like.md) | [Next: @between →](between.md)