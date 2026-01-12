# @belongsTo
The `@belongsTo` directive defines an inverse relationship to `@hasMany`.
It allows you to fetch the parent entity associated with a given record.

## Usage
```graphql
type Post {
    user: User @belongsTo
}
```
In this example, the `user` field will return the `User` record associated
with the parent `Post`.

#### Arguments
| Name       | Type   | Description                                  |
| ---------- | ------ | -------------------------------------------- |
| target     | String | Optional: related entity class or name       |
| foreignKey | String | Optional: override the foreign key column    |
| ownerKey   | String | Optional: override the referenced key column |

### Notes
- `@belongsTo` must be applied to fields returning a single object
- By default, Lumina infers relationship keys based on naming conventions
- Can be combined with directives like `@where` or `@orderBy` on the related field

---
[← Previous: @paginate](paginate.md) | [Next: @join →](join.md)

