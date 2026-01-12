# @create

The `@create` directive marks a mutation as a create operation. It allows
you to persist a new record based on the provided input without writing
a custom resolver.

## Usage
```graphql
type Mutation {
    createUser(input: CreateUserInput!): User @create
}
```
In this example, the `createUser` mutation will create a new `User`
record using the values provided in `CreateUserInput`.

#### Arguments
The `@create` directive does not accept any arguments.

### Notes
- `@create` must be applied to mutation fields
- The mutation must accept an input object
- Field names in the input type should match entity properties
- Can be combined with directives like `@validate` for input validation

---
[← Previous: @join](belongs-join.md) | [Next: @update →](update.md)

