# VELORA — GITHUB & ENGINEERING WORKFLOW

## 1. Organization

GitHub organization:

`velora-labs`

Primary repo:

`velora-saas`

Keep the initial system in one repo.

## 2. Branching

Recommended:

- `main`
- short-lived `feature/*`
- `fix/*`
- `security/*`
- `hotfix/*`

Keep branches small.

## 3. Main branch

Main should always:

- install
- build
- test
- pass static checks
- contain no secrets

## 4. Commits

Use:

- feat
- fix
- refactor
- security
- test
- docs
- perf
- chore

Examples:

`feat: add organization tenancy`

`security: enforce client policy by organization`

`test: cover cross-tenant access`

## 5. Pull requests

Every significant change should document:

- problem
- solution
- database changes
- security impact
- tests
- screenshots if UI changed
- migration notes

## 6. Issues

Recommended labels:

- `priority:p0`
- `priority:p1`
- `priority:p2`
- `type:bug`
- `type:feature`
- `type:security`
- `type:refactor`
- `module:identity`
- `module:crm`
- `module:scheduling`
- `module:finance`

## 7. Releases

Tag meaningful releases.

Do not use version numbers merely to make activity look impressive.

## 8. Documentation rule

A deliberate architecture change updates:

1. source of truth
2. relevant design document
3. decisions log
4. progress

## 9. Definition of complete

No feature is complete from a GitHub perspective until:

- code committed
- tests committed
- migrations reviewed
- docs updated
- working tree clean
- CI passing
