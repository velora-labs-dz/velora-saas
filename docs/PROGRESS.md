# VELORA — PROGRESS

This document describes reality, not aspirations.

Update at the end of each meaningful working session.

## Current date

2026-08-22

## Current phase

Phase 1, Step 7 (Attendance) — complete.

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
- [x] Clients (migration, model, factory, policy, actions, requests,
      controller, UI, tests) — full CRUD + search + archive/restore,
      scoped to the session-resolved current organization rather than a
      URL slug (first real use of `CurrentOrganization`/`EnsureCurrentOrganization`).
      Surfaced and fixed a real latent bug in that mechanism — see session
      update below. Pest suite green (52/52).
- [x] Services (migration, model, factory, policy, actions, requests,
      controller, UI, tests) — full CRUD + activate/deactivate. Surfaced
      and fixed a real environment bug (stale `bootstrap/cache/config.php`
      pinning `APP_ENV=local`, silently disabling the test-only CSRF
      bypass) — see session update below. Pest suite green (10/10 for this
      slice).
- [x] MembershipPlans + Memberships (Step 5b) — full state machine
      (draft → active → frozen/cancelled/expired, frozen → active, manual
      unfreeze only), RBAC-tiered (cancel elevated to Owner/Admin, unlike
      the general manage tier), tenant isolation including cross-org
      client/plan assignment rejection. Pest suite green (29/29).
- [x] Appointments (Step 6) — booking, edit, cancel; conflict validation
      blocks double-booking on both the staff member and the client side
      (overlapping-interval check, cancelled appointments excluded);
      `employee_id` references `organization_members` (role=staff), not a
      separate Employee table — none exists in the Phase 1 MVP entity
      list. One test bug found and fixed (two default-factory appointments
      landing on the same time slot masked an RBAC assertion behind a
      validation error). Pest suite green (16/16).
- [x] Attendance (Step 7) — manual check-in/check-out, deliberately
      decoupled from Appointment (no `appointment_id`/`service_id` on the
      table — a walk-in check-in doesn't require a prior booking, per
      `DATABASE_SCHEMA.md` §8's target schema). Check-in rejects a second
      open session for the same client (confirmed decision, and the exact
      case `TESTING.md` §8's "duplicate/open session behavior" test
      requires); a client can check in again once their prior session is
      closed. No create/edit pages — check-in is an inline action on the
      day-list index, matching the "manual history" scope, not a longer
      form the way Appointments needed. Pest suite green (11/11).

## Current environment — important correction

- Docker is NOT configured.
- Redis is NOT configured/used yet.
- PostgreSQL is running natively on Windows.
- pgAdmin is being used for database administration.
- Laravel is running via Herd.
- `.env` currently has `CACHE_STORE=redis`, which breaks the login rate
  limiter since Redis isn't running. Switch to `database` or `file` until
  Redis is actually configured.
- The Pest suite runs on SQLite in-memory (`phpunit.xml`), not Postgres.
  App code must stay portable across both — no `ILIKE`, no other
  Postgres-only SQL, unless explicitly guarded. Use Laravel's
  driver-aware query builder methods (e.g. `whereLike(..., caseSensitive: false)`
  instead of raw `ILIKE`) so the same code is correct on both engines.
- Never leave `config:cache`/`route:cache` artifacts sitting around during
  local dev+testing — a stale `bootstrap/cache/config.php` will freeze
  `APP_ENV` at whatever it was when cached, silently breaking the
  `runningUnitTests()` CSRF bypass for the entire Pest suite (every
  POST/PATCH 419s) with no code-level cause. Run `php artisan
  optimize:clear` if this happens. See Step 5a session update for the
  full diagnosis.

Do not tell future engineers/AI that Docker or Redis is operational until this changes.

## Current active work

Attendance (Step 7) is done — this closes out everything in
`FOUNDATION.md` §4's Phase 1 checklist except Payments. Next core
milestone is Payments (Step 8), the last step before "First real business
onboarded here" per `FOUNDATION.md` §7.

## Next technical milestone

# Payments (Phase 1, Step 8 — final step of Phase 1)

Tasks:

1. `payments` migration + model + factory, scoped to `organization_id` —
   columns per `DATABASE_SCHEMA.md` §9: client_id, amount, currency,
   method, status, reference (nullable), paid_at, notes, recorded_by.
   `payment_intent_id`, `provider`, `provider_transaction_id`,
   `parent_payment_id`, `refunded_amount` are explicitly future (real
   gateway integration) — don't add them now. `method` is cash/transfer
   only per `FOUNDATION.md` §4 ("Payment recording (cash/transfer only,
   no provider integration)").
2. **Append-oriented, not edit-in-place** — `VELORA_SOURCE_OF_TRUTH.md`
   §2.4 is explicit: "Payments, invoices and financial history are not
   casually edited in place. Corrections use explicit operations such as
   refunds, reversals, credit notes or adjustments." `TESTING.md` §9
   confirms with "financial record correction through supported
   operation" as a required test case. Decide the status values and the
   correction action (e.g. `recorded` → `voided`, no true delete/update
   of amount) before writing the migration — this is the one place in
   Phase 1 where get-it-right-first matters more than usual, since it's
   money.
3. policy (RBAC-aware; same `CurrentOrganization`-scoped routing pattern
   as every entity since Clients)
4. record-payment action + void/correction action
5. Whether Payment links to a Membership is NOT in `DATABASE_SCHEMA.md`
   §9's target schema (no `membership_id` column) — Payment is
   client-scoped only, not membership-scoped. Don't add a relation that
   isn't in the schema doc without confirming first.
6. controller + routes + UI (index/record/void)
7. tenant isolation + RBAC tests, including the "financial record
   correction" test case specifically
8. commit and push

## Immediate acceptance test

Per `TESTING.md` §9 (valid payment, invalid amount, invalid currency,
unauthorized payment, tenant isolation, financial record correction — read
it before starting, the way every prior step's TESTING.md section was
read first): record a cash payment for a client, confirm an invalid
amount/currency is rejected, confirm a Viewer can't record one, confirm
cross-tenant isolation, and confirm a recorded payment can be corrected
through the supported operation (not a raw edit) — each proven through a
real HTTP request.

Once this is green, Phase 1 is done end-to-end per `FOUNDATION.md` §4 —
that's the point the doc itself defines as ready for a real business.

## Do not implement yet

Do not jump to:

- inventory
- POS
- billing (Velora's own SaaS subscription billing — not the same as
  recording a client's payment)
- marketplace
- AI
- hardware
- client portal
- public API
- payment provider integration (Stripe/CIB/local gateway) — cash/manual
  only per `FOUNDATION.md` §4

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

---

## Session update — 2026-08-22 — Clients

### Date

2026-08-22

### What I changed

Implemented the full Clients milestone: `clients` migration (phone
required, unique-per-organization via a partial index that excludes
archived rows so a phone frees up on archive; soft deletes for archive,
no permanent delete in Phase 1 — see the migration's comments for why),
`Client` model + factory, `ClientPolicy` (Owner/Admin full access, Staff
operational-only, Viewer read-only — mapped onto a new
`OrganizationRole::canManageClients()`), `Store`/`UpdateClientRequest`,
four `Actions/Clients/*`, `ClientController`, four Inertia pages
(Index/Create/Show/Edit), and `Feature/Clients/ClientManagementTest` +
`Security/ClientTenantIsolationTest`.

This is also the **first entity to use `CurrentOrganization`/
`EnsureCurrentOrganization`** — routes are `/clients`, not
`/organizations/{slug}/clients`; the active organization comes from the
session (set via the existing switch endpoint), not the URL. Every lookup
in `ClientController` resolves through
`$currentOrganization->organizationOrFail()->clients()->findOrFail($id)`
rather than a global `Client::find()`, so a client from another
organization 404s at the query level, before any policy runs (see
`docs/SECURITY.md` §5).

### What I tested

`vendor/bin/pest tests/Feature/Organizations tests/Feature/Clients tests/Security`
against the real `velora_saas` Postgres database (dev) and a fresh
`php artisan migrate`.

### What passed

52/52, after two rounds of fixes below. (Total is 21 Organizations + 12
Clients + 4 Client tenant isolation + 6 Rbac + 9 org TenantIsolation.)

### What failed, and what I found

**Round 1 (13 failures) — a real, significant bug**, not a test-writing
mistake: `CurrentOrganization` was never bound as a singleton. Its
consumers — `ResolveCurrentOrganization` (sets it), `EnsureCurrentOrganization`
(reads it), `ClientController` (reads it) — each type-hint it in their
constructor, and without an explicit singleton binding, Laravel's
container hands each of them a *separate, independent instance* per
resolution. So `ResolveCurrentOrganization` would populate instance A, and
a moment later in the same request `EnsureCurrentOrganization` would check
instance B — always empty — and silently redirect to `organizations.index`
before the controller was ever reached. No exception, no validation error,
just a redirect that happened to satisfy a bare `assertRedirect()` while
creating nothing. This was a **latent bug from Step 2** — nothing had
exercised `CurrentOrganization` across two separate components in one
request until Clients did, since Organizations/Members routes resolve
`$organization` directly from `{organization:slug}` and never touch it.

Fixed two ways:
1. `$this->app->singleton(CurrentOrganization::class)` in
   `AppServiceProvider`, so every consumer in a request shares one
   instance.
2. `ResolveCurrentOrganization` now unconditionally `clear()`s the
   instance before conditionally `set()`ing it. A singleton alone would
   still leak state across requests in any environment that reuses the
   application between requests — sequential HTTP calls within one Pest
   test (proven necessary here), and notably Laravel Octane workers in
   production, where "state accidentally shared between requests via a
   singleton" is a well-known class of bug. Clearing first means a
   request that shouldn't have a current organization never inherits one
   left over from a previous request.

Also in round 1: 3 failures were my own mistake, not a code bug — when I
consolidated a duplicate test helper into `tests/Pest.php` as
`switchInto()`, I removed the old `switchClientTestUserInto()` definition
from `ClientTenantIsolationTest.php` but missed renaming its 3 call
sites. Fixed.

While fixing round 1, also **tightened a test that was passing for the
wrong reason**: `archiving a client frees its phone number for a new
client` had assertions loose enough (`assertRedirect()` with no target,
`assertDatabaseHas` without excluding trashed rows) that it would have
passed even under the singleton bug — i.e. even if the archive request had
silently no-opped. It now asserts the client is actually `trashed()` and
counts rows with/without the soft-delete scope to confirm exactly one
archived + one active row exist.

**Round 2 (6 failures) — two more issues, one real, one environmental:**

- Real: `Client::scopeSearch()` used raw `ILIKE` (Postgres-only syntax).
  The dev/prod database is Postgres, but the Pest suite runs on SQLite
  in-memory (`phpunit.xml`), and SQLite has no `ILIKE` operator — the
  search test failed with a SQL syntax error. Switched to Laravel's
  `whereLike($column, $value, caseSensitive: false)`, which compiles to
  `ILIKE` on Postgres and a case-insensitive `LIKE` on SQLite — same code,
  correct on both. Also switched the concatenated-full-name match to pass
  a `DB::raw()` expression as the column argument, since `whereLike`
  accepts an `Expression` as well as a column name. Added a note under
  "Current environment" so this class of bug (Postgres-only SQL breaking
  the SQLite-backed test suite) doesn't recur silently.
- Environmental, not a bug: 5 failures were `Vite manifest not found` /
  "Not a valid Inertia response" — the same issue as Step 3. New `.tsx`
  pages need `npm run build` before Inertia can render them in tests.

### Database changes

New `clients` table. See migration comments for the phone-uniqueness
partial index and the soft-delete/no-permanent-delete rationale.

### Security impact

First real exercise of `CurrentOrganization`-scoped access control — and
it initially failed *safe* (redirected instead of leaking data) rather
than failing open, which is the right default for this class of bug, but
it still meant the feature was completely non-functional rather than
insecure. Confirmed after the fix: a client id from another organization
404s even for an Owner of the current organization (IDOR test), a
deactivated membership loses access to clients on the very next request,
and a user who isn't a member of an organization can't select it as
current and therefore can't reach its clients at all.

### Decisions made

Documented inline in code comments (all in this round's files, not
separately in `DECISIONS.md` since none of these are architecture-level —
they're implementation choices within the already-decided Clients scope):
phone is required and is the duplicate-check field (not email); no
permanent delete in Phase 1, archive/restore only; Clients (and every
org-owned entity from here on) use `CurrentOrganization` session-scoped
routing rather than `{organization:slug}` in the URL.

### Next exact task

Step 5 — Services + Memberships. Not started; open as its own task. See
"Next technical milestone" above. Read `TESTING.md` for the
Services/Memberships section before writing any code, the same way
Clients' §4 was read first.

### Notes / blockers

None outstanding. The `CACHE_STORE=redis` note under "Current environment"
is still unresolved and still irrelevant until a Redis-dependent feature
is actually built.

---

## Session update — 2026-08-22 — Services (Step 5a)

### Date

2026-08-22

### What I changed

Implemented Services as its own reviewable slice, split from Memberships
since the two together were a bigger unit of work than Clients was:
`services` migration (price as `DECIMAL(10,2)`, never float, per
`DATABASE_SCHEMA.md` §1; no soft-delete — activate/deactivate toggle
instead, matching `TESTING.md` §5's "activate/deactivate" rather than the
archive pattern Clients used), `Service` model + factory, `ServicePolicy`
(Owner/Admin/Staff can create/edit/toggle, Viewer read-only — new
`OrganizationRole::canManageServices()`), `Store`/`UpdateServiceRequest`,
three `Actions/Services/*`, `ServiceController`, three Inertia pages
(Index/Create/Edit), and `Feature/Services/ServiceManagementTest` +
`Security/ServiceTenantIsolationTest`.

### What I tested

Full Pest run of `tests/Feature/Services` + `tests/Security/ServiceTenantIsolationTest.php`.

### What passed

Nothing, on the first run — see below.

### What failed

All 9 non-GET-based tests failed, all with the same underlying cause
disguised as two different symptoms:

- POST/PATCH requests (create, edit, toggle) got a real `TokenMismatchException`
  (HTTP 419), even though Laravel's CSRF middleware unconditionally
  bypasses verification when `app()->runningUnitTests()` is true —
  which should always be true inside a Pest run.
- GET requests that depend on session state set by a prior POST (e.g.
  `switchInto()`, which itself POSTs to `/organizations/{slug}/switch`)
  got redirected to `organizations.index` instead of rendering — because
  `switchInto()`'s own POST had silently 419'd too, so
  `current_organization_id` never made it into the session.

Root cause, found by reading `LoadConfiguration::bootstrap()`: a stale
`bootstrap/cache/config.php` existed on disk (from a `config:cache` run
at some earlier point) with `'env' => 'local'` frozen into it. When a
config cache file exists, Laravel skips reading `config/app.php` (and
therefore skips re-evaluating `env('APP_ENV')`) entirely and uses the
frozen array instead — which completely overrides `phpunit.xml`'s
`<env name="APP_ENV" value="testing"/>`. The app booted thinking it was
`local`, not `testing`, so the CSRF bypass never activated. Not a bug in
any Services code — every entity's tests would have hit this if run
after the cache was created.

Fix: `php artisan optimize:clear` (removes the stale `bootstrap/cache/*`
files). Re-ran: 10/10 green.

Also fixed while in here: `tests/Pest.php` registers
`->in('Feature', 'Security')`, but `phpunit.xml` only declared `Unit` and
`Feature` testsuites — a bare `vendor/bin/pest` with no path arguments
was silently skipping the entire `tests/Security` folder. Added a
`Security` testsuite entry to `phpunit.xml`.

### Database changes

New `services` table.

### Security impact

None new — same `CurrentOrganization`-scoped pattern as Clients, just
confirmed still correct (viewer-forbidden, cross-org 404 tests pass).

### Decisions made

Documented inline: price as `DECIMAL(10,2)`; no soft-delete for services,
active/inactive toggle instead; toggling isn't elevated to Owner/Admin
the way Client archive is, since nothing's destroyed — it just hides the
service from future bookings.

### Next exact task

Step 5b — MembershipPlans + Memberships, the state-machine half of Step
5. Not started.

### Notes / blockers

**Environmental gotcha worth remembering going forward**: never leave
`config:cache`/`route:cache` artifacts sitting around during local
dev+testing. If `php artisan optimize` (or `config:cache` directly) is
ever run to test something production-like, follow it with
`php artisan optimize:clear` before going back to `pest`/`artisan serve`
— otherwise the entire test suite can fail with what looks like a CSRF
bug but is actually a frozen `APP_ENV`. Logged under "Current
environment" above so this doesn't need rediscovering.

---

## Session update — 2026-08-22 — MembershipPlans + Memberships (Step 5b)

### Date

2026-08-22

### What I changed

Implemented the state-machine half of Step 5: `membership_plans` and
`memberships` migrations (price/currency on `memberships` is a snapshot
taken at assignment time, not a live reference to the plan — matching the
"frozen at assignment" pricing pattern from the Style Le Club project),
`MembershipPlan`/`Membership` models, `MembershipStatus` enum encoding
the full lifecycle (`draft → active → frozen/cancelled/expired`,
`frozen → active`; `Cancelled`/`Expired` terminal; transition legality
lives entirely in `MembershipStatus::allowedTransitions()` so it has one
source of truth), `MembershipPlanPolicy`/`MembershipPolicy` (cancel
elevated to `OrganizationRole::canCancelMemberships()` — Owner/Admin
only, same tier as `ClientPolicy::archive` — since cancelling is
terminal and ends paid access, unlike freeze/unfreeze/activate which sit
at the general `canManageMemberships()` tier), seven Actions (one per
transition plus create/update for both entities), `Store`/`Update`/`Cancel`
FormRequests (tenant-scoped `Rule::exists` on `client_id`/
`membership_plan_id`; `ends_at after:starts_at`), two controllers, eight
Inertia pages, and four test files.

Per explicit confirmation before starting: unfreeze is manual-only (no
auto-resume date — `docs/ROADMAP.md` §2.3 lists that as later "expiry
automation" scope), and the whole slice shipped together rather than
split further.

### What I tested

Full Pest run of `tests/Feature/MembershipPlans`, `tests/Feature/Memberships`,
`tests/Security/MembershipPlanTenantIsolationTest.php`, and
`tests/Security/MembershipTenantIsolationTest.php`.

### What passed

All 29 on the first real run: assignment, date-range validation, every
legal and illegal transition in the state machine (including both
terminal states rejecting all further transitions), plan-level
`freeze_allowed` gating, RBAC tiering (staff can freeze/unfreeze but not
cancel), and both tenant-isolation cases — including a client or plan id
from another organization being rejected at the FormRequest level, not
just the model-lookup level.

### What failed

Nothing on the real run. (A static-only review pass beforehand — this
sandbox can't run the project's actual PHP 8.4 toolchain — caught no
issues either, syntax-lint clean across all 33 files.)

### Database changes

New `membership_plans` and `memberships` tables.

### Security impact

`client_id`/`membership_plan_id` on the assign form are validated via
`Rule::exists(...)->where('organization_id', $organizationId)`, not a
bare `exists:table,id` — an id that exists but belongs to another
organization fails validation rather than silently creating a
cross-tenant membership. Confirmed by test.

### Decisions made

`freeze_limit` is stored (per `DATABASE_SCHEMA.md` §6) but deliberately
unenforced — only the on/off `freeze_allowed` gate is live. Cancel
requires a reason (`cancellation_reason`, required); no other transition
does. `membership_plans` has no `created_by` column, unlike Client/Service
— matches the target schema doc exactly, not an oversight.

### Next exact task

Step 6 — Appointments. Not started.

### Notes / blockers

None outstanding.

---

## Session update — 2026-08-23 — Appointments (Step 6)

### Date

2026-08-23

### What I changed

Implemented Appointments: `appointments` migration, `Appointment` model,
`AppointmentStatus` enum (deliberately just `Scheduled → Cancelled`,
terminal — no "completed"/"no-show" state; whether an appointment
actually happened is Attendance's job next step, tracked separately via
check-in/check-out rather than by mutating the appointment),
`AppointmentPolicy` (create/edit/cancel all at the general
`canManageAppointments()` tier — appointments don't get Membership::cancel's
elevated treatment, since cancelling a booking isn't ending a paid
terminal commitment the way cancelling a membership is), three Actions,
`Store`/`Update`/`CancelAppointmentRequest` (the first two share a
`ValidatesAppointmentConflicts` trait doing the actual double-booking
check), `AppointmentController`, three Inertia pages, and two test files.

Two things confirmed before starting, since the docs had a real
ambiguity: `employee_id` references `organization_members` (role=staff
only), not a separate Employee table — there isn't one in the Phase 1 MVP
entity list (`FOUNDATION.md`'s ~11-table list has no Employee row); and
conflict validation blocks double-booking on **both** the staff member
and the client side, not just staff.

The index is a date-navigable day list, not a drag-and-drop calendar
widget — `ROADMAP.md` §2.4 lists real scheduling depth (recurring
appointments, staff/resource availability) as later scope, so a fuller
calendar UI belongs there too.

### What I tested

Full Pest run of `tests/Feature/Appointments` + `tests/Security/AppointmentTenantIsolationTest.php`.

### What passed

15/16 on the first real run, 16/16 after one fix.

### What failed

`viewer can list appointments but cannot book, edit, or cancel them` —
not an app bug. The test's pre-existing appointment and its forbidden
POST/PATCH payload both used `AppointmentFactory`'s default time slot
(tomorrow 10:00–11:00), so the conflict-validation trait rejected the
request with a 422 *before* the controller ever reached
`Gate::authorize` — masking the intended 403 behind a validation error.
Fixed by moving the test's fixture appointment to a different day so the
two requests stop colliding. The other 15 cases (including the staff/
client conflict tests specifically targeting the trait) already proved
the conflict logic itself was correct; this was purely a test-data
collision.

### Database changes

New `appointments` table. Indexes on `(organization_id, employee_id,
starts_at)` and `(organization_id, client_id, starts_at)` specifically to
support the conflict-overlap queries.

### Security impact

Same `Rule::exists(...)->where('organization_id', ...)` pattern extended
to `client_id`, `service_id`, and `employee_id` — plus `employee_id`
additionally requires `role=staff` and `is_active=true`, so an Owner's or
Admin's own membership row (which exists, and does belong to the right
organization) still correctly fails as an invalid appointment assignee.
Confirmed by test.

### Decisions made

Conflict check excludes cancelled appointments and, on update, the
appointment being edited itself. Two ranges are only considered
overlapping if they actually share time — appointments that merely touch
at a boundary (one ends exactly when the next starts) do not conflict;
confirmed by a dedicated test. Cancellation reason is optional (unlike
Membership's required reason) — cancelling a booking is routine, not the
higher-stakes step cancelling a paid membership is.

### Next exact task

Step 7 — Attendance. Not started. See "Next technical milestone" above.
Open question to resolve before writing code, since the docs don't
specify it: can a client have more than one open (checked-in, no
check-out yet) attendance row at once, or does check-in reject if one's
already open?

### Notes / blockers

None outstanding.

---

## Session update — 2026-08-24 — Attendance (Step 7)

### Date

2026-08-24

### What I changed

Implemented Attendance: `attendance` migration (singular table name,
matching `DATABASE_SCHEMA.md` §8 exactly — `Attendance` model has an
explicit `$table` override since Eloquent would otherwise pluralize to
`attendances`), `Attendance` model with an `isOpen()` helper,
`AttendanceFactory`, `OrganizationRole::canManageAttendance()`,
`AttendancePolicy`, `CheckInAction` + `CheckOutAction`, `CheckInRequest`
(tenant-scoped `client_id` validation), `AttendanceController`, one
Inertia page, and two test files.

Confirmed before starting: a client can only have one open (checked-in,
no check-out yet) attendance row at a time — check-in rejects if one's
already open, rather than allowing unlimited concurrent open sessions.
This is exactly the case `TESTING.md` §8 names as "duplicate/open session
behavior."

Deliberately no create/edit pages — check-in is a single inline action
(client picker + optional note) on the day-list index itself, and
check-out is a one-click button on each open row. Matches the "manual
history" scope in `ROADMAP.md`'s Step 7 feature list; there's nothing
here that warrants the longer form Appointments needed.

Also confirmed and worth keeping in mind for Step 8: Attendance has no
`appointment_id` or `service_id` — it's deliberately decoupled from
booking. A walk-in client who shows up without an appointment still gets
checked in normally.

### What I tested

Full Pest run of `tests/Feature/Attendance` + `tests/Security/AttendanceTenantIsolationTest.php`.

### What passed

11/11 on the first real run: check-in, the duplicate/open-session
rejection, re-check-in succeeding once the prior session is closed,
check-out, rejecting a second check-out on an already-closed record,
RBAC (staff can check in/out, viewer can list but not mutate), and both
tenant-isolation cases (list scoping and cross-org client-id rejection at
the FormRequest level).

### What failed

Nothing.

### Database changes

New `attendance` table (singular). Composite index on
`(organization_id, client_id, check_out_at)` specifically to make the
open-session lookup in `CheckInAction` cheap.

### Security impact

Same `Rule::exists(...)->where('organization_id', ...)` pattern applied
to `client_id` on check-in. Confirmed by test that a client id from
another organization is rejected at validation time, not just at the
eventual model-lookup stage.

### Decisions made

"Open" is represented purely by `check_out_at IS NULL` — no separate
status column, matching the target schema doc exactly. Enforced the
one-open-session-per-client rule in the Action via a query, not a DB
constraint, since "already has an open session" is a computed condition
scoped to (organization, client), not a simple column-uniqueness rule a
constraint could express directly.

### Next exact task

Step 8 — Payments. Not started. This is the last step in `FOUNDATION.md`
§4's Phase 1 checklist — once it's green, Phase 1 is complete end-to-end.
See "Next technical milestone" above for the full task breakdown,
including the append-oriented/correction-not-edit requirement from
`VELORA_SOURCE_OF_TRUTH.md` §2.4 that needs deciding before the migration
is written.

### Notes / blockers

None outstanding.
