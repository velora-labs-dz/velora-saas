# VELORA — PROGRESS

This document describes reality, not aspirations.

Update at the end of each meaningful working session.

## Current date

2026-08-22

## Current phase

Phase 1, Step 3 (RBAC) — complete.

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
- [x] RBAC (member management actions/requests/controller/UI, expanded
      `OrganizationPolicy`, `RbacTest`, `MemberManagementTest`) — full
      permission matrix from `TESTING.md` implemented and verified, Pest
      suite green (36/36 once frontend assets were built). See session
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

RBAC is done. Landing page work continues in parallel as presentation-only
work, not core SaaS architecture. Next core milestone is Clients (Step 4) —
not started.

## Next technical milestone

# Clients (Phase 1, Step 4)

Tasks:

1. `clients` migration, scoped to `organization_id`
2. `Client` model + factory
3. policy (tenant-scoped, RBAC-aware per the Step 3 matrix)
4. create / update / archive-or-delete per policy
5. search + list
6. basic notes
7. tenant isolation tests (read/create/update/delete per `TESTING.md` §4)
8. commit and push

## Immediate acceptance test

Per `TESTING.md` §4 (Client tests): create, edit, archive/delete according
to policy, duplicate handling, search, unauthorized access, cross-tenant
access — each proven through a real HTTP request, not just a passing
assertion in isolation.

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

---

## Session update — 2026-08-22 — RBAC

### Date

2026-08-22

### What I changed

Implemented the full RBAC milestone on top of Step 2's tenancy boundary:
member management (list/add/change-role/remove/leave) with the permission
matrix `TESTING.md` §3 requires.

**Actions:** `AddOrganizationMemberAction`, `UpdateOrganizationMemberRoleAction`,
`RemoveOrganizationMemberAction` — the last two enforce the "an organization
must always have at least one active Owner" invariant via a locking read
(`lockForUpdate()`), not just a policy check, so it's race-safe against two
concurrent demotions/removals.

**Policy (`OrganizationPolicy`):** `viewMembers` (any active member),
`addMember` / `updateMemberRole` / `removeMember` (Owner/Admin only, and
only an Owner may grant Owner or touch an existing Owner's role — ownership
transfer is Owner-only), `leaveOrganization` (any active member, for
themselves only).

**Routes/UI/Controller:** `OrganizationMemberController`
(index/store/update/destroy/leave), `Organizations/Members.tsx`.

**Tests:** `tests/Feature/Organizations/MemberManagementTest.php`,
`tests/Security/RbacTest.php` covering the full Owner/Admin/Staff/Viewer
matrix plus self-role-change and last-owner-standing edge cases.

### What I tested

`vendor/bin/pest tests/Feature/Organizations tests/Security` against the
real `velora_saas` Postgres database.

### What passed

35/36 on the first full run. A second run (after `npm run build` fixed an
unrelated "Vite manifest not found" issue on 5 Inertia-rendering tests)
brought the suite to 36/36 minus the one real RBAC bug below — 35 passing,
1 failing, until the fix landed.

### What failed

One real bug: `the sole remaining owner cannot be demoted` failed with
"Session is missing expected key [errors]" — the response wasn't the
expected 422.

**Root cause:** `OrganizationPolicy::updateMemberRole()` had a blanket
`if ($target->user_id === $user->id) return false;` self-check. That's too
broad — it blocked *every* self-role-change (including a sole Owner
demoting themselves) with a 403, before the request ever reached
`UpdateOrganizationMemberRoleAction`'s "must have at least one Owner"
validation logic, so the test never got the `role` session error it
expected.

**Why the blanket check was redundant:** self-privilege-*escalation* (a
non-Owner granting themselves Owner) was already independently blocked by
the ownership-transfer rule a few lines below (only an existing Owner may
grant/touch Owner). A self-*demotion* (Owner stepping down to Admin) isn't
an escalation and shouldn't be a 403 — it should hit the same "must always
have one Owner" invariant that already existed in the Action, whether the
target is the actor or someone else.

**Fix:** removed the blanket self-check from `updateMemberRole`. Verified
this doesn't reopen `nobody can change their own role` (Admin self-promoting
to Owner) — that case is still correctly blocked by the ownership-transfer
rule alone, since granting Owner still requires the actor to already be an
Owner.

Not yet re-run locally (no PHP available in this session) — expecting
36/36 on next run; report back the output.

### Database changes

None — RBAC is enforced entirely at the policy/action layer on top of the
existing `organization_members` schema.

### Security impact

Closes a gap where the last-Owner-protection invariant could be bypassed
via a false-negative *authorization* failure masking what should be a
*validation* failure — functionally the org was still protected (request
was rejected either way), but the failure mode was wrong, which is exactly
the kind of thing that hides a real bug behind a superficially-passing
security posture. Confirms self-privilege-escalation to Owner is still
blocked through the ownership-transfer rule, independent of the removed
self-check.

### Decisions made

None requiring a new ADR — this is a bugfix to the RBAC rule set specified
in `TESTING.md` §3, not a change in architecture or direction.

### Next exact task

Step 4 — Clients. Not started; open as its own task. See "Next technical
milestone" above.

### Notes / blockers

Re-run `vendor/bin/pest tests/Feature/Organizations tests/Security` locally
to confirm 36/36 before starting Step 4.
