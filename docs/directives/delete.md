# @delete
The `@delete` directive marks a mutation as a delete operation. It allows
you to remove a record from the database without writing a custom resolver.

## Usage
```graphql
type Mutation {
    deleteUser(id: ID!): User @delete
}
```
In this example, the `deleteUser` mutation will delete the `User` record
identified by `id` and return the deleted record.

#### Arguments
| Name    | Type   | Description                                            |
| ------- | ------ | ------------------------------------------------------ |
| idField | String | Optional: name of the identifier field (default: `id`) |

### Notes
- `@delete` must be applied to mutation fields
- The mutation must include an identifier argument
- Can be combined with directives like `@validate` for input validation

---
[← Previous: @update](update.md) | [Next: @validate →](validate.md)

