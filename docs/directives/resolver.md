# @resolver

The `@resolver` directive allows you to attach a custom resolver function
to a field. It is useful when the default directives cannot handle
your specific business logic.

## Usage

```graphql
type Query {
    specialUsers: [User] @resolver(function: "App\\GraphQL\\Resolvers\\SpecialUsersResolver@resolve")
}
```
In this example, the `specialUsers` query will execute the
`resolve` method of the `SpecialUsersResolver` class to fetch data.

### Resolver Context
Your resolver method can access the following automatically if needed:
- $root — the parent/root value of the field
- $arguments — the arguments passed to the field
- $context — the GraphQL context (auth, services, etc.)
- $info — GraphQL query info (field selections, path, etc.)

You don’t need to declare all of them; just include the ones your logic requires.

#### Arguments
| Name     | Type   | Description                                           |
| -------- | ------ | ----------------------------------------------------- |
| function | String | Required: fully qualified class and method to execute |

### Notes
- The resolver function can return either:
  - A single entity or object
  - An array of entities
- Useful for complex queries, third-party API calls, or custom computation
- Lumina will automatically normalize the return value to match the GraphQL type
- Can be used alongside other directives for filtering, `@pagination`, or `@orderBy`
- Keep business logic in resolvers, not in schema definitions

---
[← Previous: @avg](avg.md) | [Next: @can →](can.md)

