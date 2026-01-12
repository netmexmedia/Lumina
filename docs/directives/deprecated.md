# @deprecated

The `@deprecated` directive marks a field or type as deprecated. It
indicates to clients that the field or type should no longer be used,
and optionally provides a reason.

## Usage
```graphql
type User {
    oldField: String @deprecated(reason: "Use newField instead")
    newField: String
}
```
In this example, `oldField` is marked as deprecated. GraphQL clients
will see a warning if they query this field.

#### Arguments
| Name   | Type   | Description                           |
| ------ | ------ | ------------------------------------- |
| reason | String | Optional: explanation for deprecation |

### Notes
- Can be applied to fields and types
- Useful for gradually removing or replacing fields without breaking clients
- Clients and tools can use the reason to provide guidance
- Does not affect runtime behavior; it is purely informational

---
[← Previous: @role](role.md) | [Next: Creating a directive →](creating-a-directive.md)

