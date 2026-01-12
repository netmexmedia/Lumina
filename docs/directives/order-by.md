# @orderBy
The `@orderBy` directive allows you to sort query results by a specific
field and direction. It is commonly used to control the order in which
records are returned.

## Usage
```graphql
type Query {
    users: [User] @all @orderBy(field: "createdAt", direction: "DESC")
}
```
In this example, the `users` query will return `User` records ordered by
the `createdAt` field in descending order.

#### Arguments
| Name      | Type   | Description                                |
| --------- | ------ | ------------------------------------------ |
| field     | String | Required: field to sort by                 |
| direction | String | Optional: sort direction (`ASC` or `DESC`) |

### Notes
- The default sort direction is `ASC`
- Multiple `@orderBy` directives may be applied to define compound sortin
- Can be used alongside other directives like `@all`, `@orderBy`, `@paginate`

---
[← Previous: @between](between.md) | [Next: @limit →](limit.md)

