# @avg
The `@avg` directive calculates the average of a numeric field across a
set of records. It is useful for reporting, analytics, and derived
metrics.

## Usage
```graphql
type Query {
    averageOrderAmount: Float @avg(model: "Order", field: "amount")
}
```
In this example, the `averageOrderAmount` query will return the average
value of the `amount` field for all `Order` records.

#### Arguments
| Name  | Type   | Description                                    |
| ----- | ------ | ---------------------------------------------- |
| model | String | Required: fully qualified model or entity name |
| field | String | Required: field to average                     |

### Notes
- `@avg` works only with numeric fields
- an be combined with directives like `@where` to calculate averages
  on filtered results
- Useful for metrics, dashboards, and reporting

---
[← Previous: @sum](sum.md) | [Next: @resolver →](resolver.md)

