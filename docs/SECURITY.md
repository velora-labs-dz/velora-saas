# VELORA — SECURITY REQUIREMENTS

## 1. Security objective

A customer must be able to trust Velora with operational and financial business information.

The most important property is:

> Organization A can never access Organization B's data.

## 2. Authentication

Required:

- secure password storage
- login
- logout
- password reset
- session invalidation
- email verification when enabled
- CSRF protection
- secure cookies
- appropriate session timeout
- future MFA for sensitive roles

## 3. Authorization

Every mutation must pass server authorization.

Required:

- Organization membership check
- Policy authorization
- Role/permission authorization
- object ownership/scoping

The frontend is not a security boundary.

## 4. Tenant isolation

Every organization-owned table:

- has organization_id
- is scoped by current organization
- has authorization
- has isolation tests

Test:

```text
Alice → Org A = allowed
Alice → Org B = denied
```

including:

- SELECT
- INSERT
- UPDATE
- DELETE
- file access
- indirect references
- exports
- reports

## 5. IDOR

Never trust URLs, hidden form fields, or JSON organization IDs.

For every resource:

1. resolve through current organization
2. authorize the action
3. operate on the authorized record

## 6. Mass assignment

Use explicit validation.

Never allow request payloads to set protected fields such as:

- organization_id
- owner
- role
- subscription status
- payment status
- audit actor

unless the action specifically authorizes them.

## 7. Roles

Initial:

- Owner
- Admin
- Staff
- Viewer

Role assignment is organization-scoped.

Platform admin is separate.

## 8. Platform admin

Platform administrators must not be self-grantable through customer-facing routes.

Every platform action must be server-authorized.

Sensitive platform actions should be audited.

## 9. Financial security

Money must not be edited casually.

Required patterns:

- transactions
- authorization
- immutable history where appropriate
- refunds/reversals
- audit
- idempotency for external payment operations

## 10. File security

Files containing customer/business information must be private.

Object paths should include organization scope.

Use authenticated access or short-lived signed URLs.

## 11. Secrets

Secrets must never be committed.

Use `.env` locally and secure secret storage in deployment.

`.env.example` contains placeholders only.

## 12. Rate limiting

At minimum consider:

- login
- password reset
- public contact
- public API
- webhook endpoints
- export endpoints
- expensive reports

## 13. Webhooks

Future webhooks must use:

- signature verification
- replay protection
- event IDs
- idempotency
- timestamp checks where supported

## 14. Audit

When the platform becomes commercial, audit events should cover:

- authentication security events
- membership changes
- permission changes
- financial actions
- subscription changes
- platform admin actions
- destructive operations

## 15. Security testing

Security tests are part of the feature.

Every new entity must include a tenant isolation test.

Every new mutation must include an authorization test.

## 16. Dependency security

Regularly audit:

- Composer packages
- npm packages
- runtime versions
- PHP
- Laravel

Do not update blindly; update through CI and regression tests.

## 17. Incident response

If a vulnerability is suspected:

1. contain
2. preserve evidence
3. disable affected capability if required
4. determine affected tenants
5. fix
6. rotate credentials if necessary
7. verify
8. document postmortem
