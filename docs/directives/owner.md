# @owner
The `@owner` directive restricts access to a field or object so that
users can only access records they “own.” Ownership is determined by
matching the entity’s owner field with the current user’s identifier.

## Usage
```graphql
type Query {
    myPosts: [Post] @all @owner
}
```
In this example, the `myPosts` query will return only the posts that
belong to the currently authenticated user.

#### Arguments
The @owner directive does not accept any arguments.

### Notes
- Can be applied to both fields and object types
- Works automatically with the authenticated user context
- Can be combined with other directives like `@can` or `@where`
- Useful for multi-tenant applications or user-specific data access

---
[← Previous: @role](role.md) | [Next: @deprecated →](deprecated.md)

