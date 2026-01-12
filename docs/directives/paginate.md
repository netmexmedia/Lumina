# @paginate
The `@paginate` directive allows you to return results in a paginated
format. It is useful for building APIs that need page-based navigation
with metadata such as total counts and page sizes.

## Usage

```graphql
type Query {
    users: UserPagination @paginate
}
```
In this example, the `users` query will return paginated `User` results
instead of a simple list.

#### Arguments
| Name | Type | Description                        |
| ---- | ---- | ---------------------------------- |
| page | Int  | Optional: page number (default: 1) |
| size | Int  | Optional: number of items per page |


### Notes
- `@paginate` changes the return type from a list to a pagination object
- Commonly used instead of `@limit` and `@offset`

---
[← Previous: @limit](limit.md) | [Next: @hasMay →](has-many.md)

