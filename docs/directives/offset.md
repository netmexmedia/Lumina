# @offset
The `@offset` directive allows you to skip a specified number of records
before returning results. It is commonly used in combination with
`@limit` to implement offset-based pagination.


## Usage
```graphql
type Query {
    users: [User] @all @offset(value: 20)
}
```
In this example, the `users` query will skip the first 20 `User` records
and return the remaining results.

#### Arguments
| Name  | Type | Description                         |
| ----- | ---- | ----------------------------------- |
| value | Int  | Required: number of records to skip |

### Notes
- `@offset` applies only to list-returning fields
- Often used together with `@limit` for pagination
- Can be used alongside other directives like `@all`, `@orderBy`, `@paginate`

---
[← Previous: @limit](limit.md) | [Next: @paginate →](paginate.md)

