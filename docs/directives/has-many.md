# @hasMany
The `@hasMany` directive defines a one-to-many relationship between two
types. It allows you to fetch related records directly through the
GraphQL schema.

## Usage
```graphql
type User {
    posts: [Post] @hasMany
}

```
In this example, the `posts` field will return all `Post` records related
to the parent `User`.

#### Arguments
| Name       | Type   | Description                               |
| ---------- | ------ | ----------------------------------------- |
| target     | String | Optional: related entity class or name    |
| foreignKey | String | Optional: override the foreign key column |
| localKey   | String | Optional: override the local key column   |

### Notes
- `@hasMany` applies only to list-returning fields
- By default, Lumina infers key names based on naming conventions
- Can be combined with directives like `@where`, `@orderBy`, or `@paginate`

---
[← Previous: @paginate](paginate.md) | [Next: @belongsTo →](belongs-to.md)

