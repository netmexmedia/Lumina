# @count
The `@count` directive allows you to return the number of records for a
given field or query. It is useful for aggregation and reporting
without fetching full data sets.

## Usage
```graphql
type Query {
    totalUsers: Int @count(model: "User")
}
```
In this example, the `totalUsers` query will return the total number of
`User` records in the database.

#### Arguments
| Name  | Type   | Description                                    |
| ----- | ------ | ---------------------------------------------- |
| model | String | Required: fully qualified model or entity name |

### Notes
- returns an integer representing the number of records
- Can be combined with directives like` @where` for filtered counts

---
[← Previous: @validate](validate.md) | [Next: @sum →](sum.md)

