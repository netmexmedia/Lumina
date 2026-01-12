# @validate
The `@validate` directive allows you to attach a validation class to a
mutation input. The validator class handles input validation before
the mutation is executed, ensuring that data meets your application’s
requirements.

## Usage
```graphql
type Mutation {
    createUser(input: CreateUserInput! @validate(class: "App\\Validators\\CreateUserValidator")): User @create
}
```
In this example, the `createUser` mutation will use the
`CreateUserValidator` class to validate the input before creating
the `User` record.

#### Arguments
| Name  | Type   | Description                                           |
| ----- | ------ | ----------------------------------------------------- |
| class | String | Required: fully qualified class name of the validator |


### Notes
- Validator classes must implement Lumina's `ValidatorInterface`
- Ensures consistent validation logic across mutations
- Can be used alongside` @create`, `@delete`, or` @delete` mutations
- Custom logic such as complex field checks or cross-field validation
  should be implemented inside the validator class

---
[← Previous: @delete](delete.md) | [Next: @count →](count.md)

