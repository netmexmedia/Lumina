# @role

The `@role` directive restricts access to a field or object based on
user roles. Only users with the specified role(s) are allowed to access
the data.

## Usage
```graphql
type Query {
    adminData: [Secret] @all @role(name: "admin")
}
```
In this example, the `adminData` query will only return records for users
who have the `admin` role.

#### Arguments
| Name | Type   | Description                               |
| ---- | ------ | ----------------------------------------- |
| name | String | Required: role name or list of role names |


### Notes
- Can be applied to both fields and object types
- Multiple `@role` directives can be used (repeatable)
- Useful for role-based access control (RBAC)
- Can be combined with other directives like `@can` or `@where`

---
[← Previous: @can](can.md) | [Next: @owner →](owner.md)

