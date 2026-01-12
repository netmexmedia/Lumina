# @between
The `@between` directive allows you to filter query results by specifying
a range of values. It is commonly used for numeric values, dates, or
timestamps.

## Usage
```graphql
type Query {
    users(createdAt: DateRange @between): [User] @all
}
```
In this example, the `users` query will return all `User` records where
the `createdAt` value falls within the provided range.

#### Arguments
| Name   | Type   | Description                            |
| ------ | ------ | -------------------------------------- |
| column | String | Optional: override the database column |
| min    | Any    | Required: minimum value of the range   |
| max    | Any    | Required: maximum value of the range   |

### Notes
- `@in` must be used on arguments, not on fields directly
- Supports scalar types (strings, integers, etc.)
- Can be used alongside other directives like `@all`, `@orderBy`, `@paginate`

---
[← Previous: @in](in.md) | [Next: @between →](order-by.md)