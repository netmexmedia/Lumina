# @sum
The `@sum` directive allows you to calculate the total sum of a numeric
field across a set of records. It is useful for reporting, analytics,
and computed fields.

## Usage
```graphql
type Query {
    totalRevenue: Float @sum(model: "Order", field: "amount")
}
```
In this example, the `totalRevenue` query will return the sum of the
`amount` field for all `Order` records.

#### Arguments
| Name  | Type   | Description                                    |
| ----- | ------ | ---------------------------------------------- |
| model | String | Required: fully qualified model or entity name |
| field | String | Required: field to sum                         |

### Notes
- `@sum `works only with numeric fields
- Can be combined with directives like `@where` to sum filtered records
- Commonly used in dashboards, financial reports, and metrics

---
[← Previous: @count](count.md) | [Next: @avg →](avg.md)

