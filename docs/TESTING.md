# VELORA — TESTING STRATEGY

## 1. Testing principle

Tests protect the architecture.

A feature is not complete because it works once in the browser.

## 2. Test layers

### Unit tests

Pure business rules.

### Feature tests

Laravel application workflows.

### Security tests

Tenant isolation and authorization.

### Browser/E2E tests

Critical journeys through the UI.

### Database tests

Constraints and transactional integrity.

## 3. Required Phase 1 tests

### Auth

- register
- login
- logout
- password reset
- protected route access

### Organizations

- create organization
- owner membership created
- cannot create duplicate membership
- user can switch only to organizations they belong to

### Tenant isolation

For every Phase 1 entity:

```text
Org A user → Org A = allowed
Org A user → Org B = denied
```

Test:

- read
- create
- update
- delete

### RBAC

Owner:

- full organization access

Admin:

- administrative access but not ownership transfer

Staff:

- operational mutations
- no organization administration
- no destructive actions unless explicitly permitted

Viewer:

- read only

## 4. Client tests

- create
- edit
- archive/delete according to policy
- duplicate handling
- search
- unauthorized access
- cross-tenant access

## 5. Service tests

- create
- edit
- activate/deactivate
- organization scope
- authorization

## 6. Membership tests

- create plan
- assign membership
- activate
- invalid date ranges rejected
- invalid transitions rejected
- authorization
- tenant isolation

## 7. Appointment tests

- create
- edit
- cancel
- invalid time range
- unauthorized staff
- cross-tenant client
- conflict rules as they are implemented

## 8. Attendance tests

- check-in
- check-out
- duplicate/open session behavior
- organization scope

## 9. Payment tests

- valid payment
- invalid amount
- invalid currency
- unauthorized payment
- tenant isolation
- financial record correction through supported operation

## 10. Future financial tests

When POS exists:

- successful atomic sale
- rollback after failure
- stock and payment consistency
- refund

## 11. Regression suite

After every major domain change:

- full security suite
- critical workflow suite
- database migration tests
- application boot test

## 12. Definition of done

A feature is done when:

- tests exist
- tests pass
- authorization tested
- tenant isolation tested
- validation tested
- failure paths tested
- UI verified
- documentation updated
