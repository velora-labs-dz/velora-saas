# VELORA — PROGRESS

This document describes reality, not aspirations.

Update at the end of each meaningful working session.

## Current date

2026-08-21

## Current phase

Phase 1, Step 2 (Organizations + Tenancy) — complete.

## Completed

- [x] GitHub organization `velora-labs`
- [x] Private `velora-saas` repository
- [x] Laravel 13 installed through Laravel Herd on Windows
- [x] Inertia + React + TypeScript scaffolded
- [x] Pest available
- [x] PostgreSQL database created through pgAdmin
- [x] `velora_saas` database created
- [x] Default Laravel migration executed successfully
- [x] Application and frontend development server tested
- [x] Initial project pushed to GitHub
- [x] Documentation foundation created
- [x] Landing page work started as pixel-accurate Nexora reference work
- [x] Organizations + Tenancy (migrations, models, policy, middleware,
      controller, UI, tests) — migrated and verified against the real
      `velora_saas` database, Pest suite green (20/20), manual browser
      walkthrough confirmed the cross-tenant 403 boundary. See session
      update below for detail.

## Current environment — important correction

- Docker is NOT configured.
- Redis is NOT configured/used yet.
- PostgreSQL is running natively on Windows.
- pgAdmin is being used for database administration.
- Laravel is running via Herd.
- `.env` currently has `CACHE_STORE=redis`, which breaks the login rate
  limiter since Redis isn't running. Switch to `database` or `file` until
  Redis is actually configured.

Do not tell future engineers/AI that Docker or Redis is operational until this changes.

## Current active work

Organizations + Tenancy is done. Landing page work continues in parallel as
presentation-only work, not core SaaS architecture. Next core milestone is
RBAC (Step 3) — not started.

## Next technical milestone

# Organizations + Tenancy

Tasks:

1. create `organizations`
2. create `organization_members`
3. create model relationships
4. define organization ownership
5. implement current organization context
6. implement organization switching if required
7. write tenant isolation tests
8. verify authorization boundary
9. commit and push

## Immediate acceptance test

Create:

- Alice → Organization A
- Bob → Organization B

Verify:

- Alice can access A
- Alice cannot access B
- Bob can access B
- Bob cannot access A

Test the denial through real Laravel authorization/application paths.

## Do not implement yet

Do not jump to:

- inventory
- POS
- billing
- marketplace
- AI
- hardware
- client portal
- public API

unless the roadmap phase is explicitly opened.

## Session update template

### Date

### What I changed

### What I tested

### What passed

### What failed

### Database changes

### Security impact

### Decisions made

### Next exact task

### Notes / blockers

---

## Session update — 2026-08-21 — Organizations + Tenancy

### Date

2026-08-21

### What I changed

Implemented the full Organizations + Tenancy milestone per the task list
above.

**Migrations:** `organizations`, `organization_members` (unique on
org+user, role lives on the membership not the user). Also found and fixed
an unrelated pre-existing gap: `SESSION_DRIVER=database` in `.env` had no
matching migration in the repo — turned out a `sessions` table already
existed in the real database from elsewhere, so the migration file was
deleted rather than run once that was confirmed.

**Models:** `Organization` (route key = slug, not numeric id; `status`/
`created_by` excluded from mass assignment), `OrganizationMember` (zero
fillable attributes — every row built via explicit property assignment in
trusted server code; uses the `AsPivot` trait with an explicit
`protected $table = 'organization_members'` so it can hydrate correctly as
the pivot model for the `Organization`↔`User` relationship). `User` gained
`organizations()` / `organizationMemberships()`.

**Enums:** `OrganizationRole` (owner/admin/staff/viewer),
`OrganizationStatus` (active/suspended/cancelled).

**Tenancy context:** `CurrentOrganization` (request-scoped singleton),
`ResolveCurrentOrganization` middleware (global, re-verifies the session's
org-id hint against an active `OrganizationMember` row on every request —
never trusts it directly), `EnsureCurrentOrganization` (route-scoped,
aliased `current-org`, not yet applied to any route — ready for Step 4).

**Authorization:** `OrganizationPolicy` (view/update/switchTo), queried
fresh from the DB per organization instance so it holds correctly for orgs
the user does *not* currently have selected.
`CreateOrganizationAction` runs the org+owner-membership creation in a DB
transaction; `slug`/`status`/`created_by`/`role` are never accepted from
request input (`StoreOrganizationRequest` has no rules for them).

**Routes/UI:** `OrganizationController` (index/create/store/show/switch),
routes under `auth`, minimal Inertia pages (`Organizations/Index`,
`Create`, `Show`), nav link added to `AuthenticatedLayout`.

**Tests:** `tests/Feature/Organizations/CreateOrganizationTest.php`,
`SwitchOrganizationTest.php`, and `tests/Security/TenantIsolationTest.php`
— the last covering the exact required Alice/Org A vs Bob/Org B scenario
through real HTTP requests, plus a forged-session test and
deactivated/deleted-membership revocation tests.

### What I tested

- `php artisan migrate` against the real `velora_saas` Postgres database.
- `vendor/bin/pest tests/Feature/Organizations tests/Security`.
- Full manual browser walkthrough: create org A as user 1 → view shows role
  owner correctly → register user 2 (Bob), create org B → add user 1 to org
  B as viewer via Tinker → user 1's Organizations page lists both orgs with
  correct roles → switch to B succeeds → **Bob, who is only a member of B,
  gets a 403 navigating directly to org A's URL** (the actual point of this
  milestone).

### What passed

Everything. Pest: 20/20 (39 assertions). Manual walkthrough: every step
behaved as expected, including the cross-tenant 403.

### What failed

Two real bugs surfaced and were fixed during verification (not just typos —
worth recording so they're not rediscovered):

1. `belongsToMany(...)->withPivot([...])` alone does not cast pivot
   attributes — `$organization->pivot->role` came back as a raw string, not
   the `OrganizationRole` enum, causing
   `Attempt to read property "value" on string` in the UI. Fixed with the
   `AsPivot` trait on `OrganizationMember` plus
   `->using(OrganizationMember::class)` on both sides of the relationship.
2. Adding `AsPivot` changed Eloquent's table-name guess (it skips
   pluralization for pivot-style models), so it looked for
   `organization_member` (singular) instead of the real
   `organization_members` table. Fixed by declaring
   `protected $table = 'organization_members';` explicitly.
3. Not a code bug, but blocked verification: local `.env` had
   `CACHE_STORE=redis`, and Redis isn't running locally (matches the
   documented "Redis is NOT configured" status) — broke the login rate
   limiter. Worked around by switching `CACHE_STORE` locally; flagged above
   under "Current environment" so it isn't rediscovered as a surprise.

### Database changes

`organizations` and `organization_members` tables created and confirmed
against the real `velora_saas` database.

### Security impact

New authorization boundary introduced and verified live in the browser, not
just in tests: a user with no `OrganizationMember` row for an organization
gets a 403 on that organization's URL, regardless of what the UI shows or
what's in their session. Session only ever carries an organization-id
*hint*, re-verified against the database on every request.

### Decisions made

ADR-009 — organization public identifier is the slug, not a new PK strategy
(ULID/UUID). See `DECISIONS.md`.

### Next exact task

Step 3 — RBAC. Not started; open as its own task.

### Notes / blockers

None outstanding for this milestone. See "Current environment" above for
the `CACHE_STORE=redis` note to resolve before Redis-dependent features are
built for real.
