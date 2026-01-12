# @limit
The `@limit` directive allows you to restrict the maximum number of
records returned by a query. It is useful for performance optimization
and for limiting result sizes.

## Usage
```graphql
type Query {
    users: [User] @all @limit(value: 10)
}
```
In this example, the `users` query will return at most 10 `User` records.

#### Arguments
| Name  | Type | Description                                   |
| ----- | ---- | --------------------------------------------- |
| value | Int  | Required: maximum number of records to return |


### Notes
- `@limit` applies only to list-returning fields
- Commonly used together with `@orderBy` to ensure predictable results
- Can be used alongside other directives like `@all`, `@orderBy`, `@paginate`

---
[← Previous: @oorderBy](order-by.md) | [Next: @offset →](offset.md)

