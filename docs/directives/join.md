# @join
The `@join` directive allows you to explicitly define a join between two
entities. It is useful when automatic relationship inference is not
sufficient or when you need more control over how entities are joined.

## Usage
```graphql
type Query {
    users: [User] @all @join(target: "posts")
}
```
In this example, the `users` query will join the related `posts` entity
when fetching `User` records.

#### Arguments
| Name   | Type   | Description                                 |
| ------ | ------ | ------------------------------------------- |
| target | String | Required: entity or relation to join        |
| type   | String | Optional: join type (`INNER`, `LEFT`, etc.) |

### Notes
- `@join `provides fine-grained control over join behavior
- Commonly used in advanced queries or custom schema designs
- Can be combined with directives like` @where` or `@orderBy`

---
[← Previous: @belongsTo](belongs-to.md) | [Next: @create →](create.md)

