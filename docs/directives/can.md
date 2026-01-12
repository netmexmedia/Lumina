# @can
The `@can` directive allows you to enforce permission-based access
control on fields. It checks whether the current user has the required
permission before allowing access to the data.

## Usage
```graphql
type Query {
    sensitiveData: [Secret] @all @can(permission: "view_secrets")
}
```
In this example, the `sensitiveData` query will only return records if
the current user has the `view_secrets` permission.

#### Arguments
| Name       | Type       | Description                                 |
| ---------- | ---------- | ------------------------------------------- |
| permission | [String!]! | Required: permission identifier(s) to check |


### Notes
- Can be applied to both fields and object types
- Supports multiple permission identifiers — all must pass
- Throws a `LogicException` if a permission is denied
- Useful for enforcing fine-grained, class-based authorization
- Works seamlessly with other directives like `@all`, `@where`, or `@orderBy`

---
[← Previous: @resolver](resolver.md) | [Next: @role →](role.md)

