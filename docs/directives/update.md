# @update
The `@update` directive marks a mutation as an update operation. It allows
you to modify an existing record based on the provided input without
writing a custom resolver.

## Usage
```graphql
type Mutation {
    updateUser(id: ID!, input: UpdateUserInput!): User @update
}
```
In this example, the `updateUser` mutation will update the `User` record
identified by `id` using the values provided in `UpdateUserInput`.

#### Arguments
| Name    | Type   | Description                                            |
| ------- | ------ | ------------------------------------------------------ |
| idField | String | Optional: name of the identifier field (default: `id`) |

### Notes
- `@update` must be applied to mutation fields
- The mutation must include an identifier argument
- Input field names should match entity properties
- Can be combined with directives like `@validate` for input validation

---
[← Previous: @create](create.md) | [Next: @delete →](delete.md)

