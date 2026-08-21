# VELORA_SOURCE_OF_TRUTH.md

This document is the constitution. It does not change per feature, per client request, or per mood on a Tuesday night. If a decision in this file needs to change, that change must be made deliberately, in this file, with a reason written down — not silently overridden in code somewhere.

If you (or any future Claude instance) are ever unsure what to do, this file wins over any other instinct, ChatGPT suggestion, or client pressure.

---

## 1. Non-Negotiable Rules

1. **No table without a domain reason.** If you can't say in one sentence why an entity exists, it doesn't get created.
2. **No business action without authorization.** Every mutation goes through a Policy. No exceptions, no "I'll add auth later."
3. **No financial record is ever edited in place.** Payments, invoices — corrections happen via refund/reversal/adjustment records, never `UPDATE payments SET amount = ...`.
4. **No tenant-owned data without an `organization_id` that is server-derived and server-verified.** Never trust an `organization_id` sent from the frontend. Always resolve it from the authenticated user's current organization context.
5. **No feature ships without a tenant isolation test.** Org A must never be able to read, write, or infer the existence of Org B's data — test this explicitly, per entity.
6. **No custom one-off code for a single client.** If a client needs something, it becomes a configuration option or feature flag, or it doesn't get built. (This is the rule most likely to get bent under pressure — it is the one that matters most.)
7. **Money is always `DECIMAL`, never float. Every amount has an explicit currency.** Default currency: DZD.
8. **Roles are assigned per-organization, never globally on the user.** A user can be Owner of Org A and Staff of Org B simultaneously. The `users` table never has a role column.
9. **Controllers stay thin.** Validation → Authorization → Action class → Response. Business logic does not live in controllers.
10. **A feature is not done when the screen exists.** Done = migration + policy + validation + business logic + frontend + test. All of them.

---

## 2. Naming (fixed — do not introduce synonyms)

- The customer's business = **Organization**. Never "tenant," "company," or "business" in code/UI/docs.
- The organization's customer = **Client**. Never "member," "user," or "customer" for this concept (confusing with SaaS-level users).
- The thing Velora sells to the Organization = not modeled yet (no SaaS billing in Phase 1). When it exists, it's **Subscription**, and it is a completely separate concept from **Membership** (which is Client ↔ Organization).
- Status values are stored in English, lowercase, snake_case (`active`, `frozen`, `expired`) — never store display language as state.

---

## 3. Tenancy Rule

Every table that holds organization-owned data has an `organization_id` foreign key. Every query touching that table is scoped through the authenticated user's current organization — resolved server-side, once, in middleware, not repeated ad-hoc in every controller.

The test that proves this works:
```
Alice belongs to Org A only.
Bob belongs to Org B only.

Alice → Org A clients: allowed
Alice → Org B clients: denied (403 or 404, not data leakage)
Bob   → Org B clients: allowed
Bob   → Org A clients: denied
```
This test must exist and pass before any new entity is considered complete.

---

## 4. Authorization Rule

Frontend hides buttons. Backend is the only thing that actually enforces anything. Every entity with mutation actions gets a Policy class. If a Policy doesn't exist for an entity, that entity cannot be mutated yet — write the Policy first.

Default role capabilities (4 roles for now — Owner, Admin, Staff, Viewer):
- **Owner** — full control, including organization deletion and ownership transfer (explicit workflow, never plain CRUD)
- **Admin** — manage everything except billing/ownership
- **Staff** — create/update within their scope, no delete, no admin actions
- **Viewer** — read-only, everywhere

---

## 5. What Gets Built vs What Doesn't

Build it if:
- A real business (not a hypothetical one) needs it to operate, OR
- Its absence blocks Phase 1 from being usable end-to-end

Don't build it if:
- "It would be cool" / "competitors have it" / "might need it later"
- It's solving one client's one-off request instead of a repeatable pattern

When in doubt, it goes in `LATER.md`, not in the codebase.

---

## 6. Stack Commitment

Laravel 13 + Inertia + React/TS + PostgreSQL + Redis. This was chosen deliberately after considering NestJS/Next.js (used on Style Le Club) and picking Laravel intentionally for this project. Do not re-litigate the stack mid-build. If it's genuinely wrong, that's a documented decision here, not a silent pivot three weeks in.

---

## 7. Relationship to Past Work

Nexora and Style Le Club are prior work, not this project's foundation. Lessons carry over (tenant scoping, role-based permission checks, atomic sequential numbering for receipts/invoices via a counters-style service, mixed-payment handling) — but no code, tables, or architecture gets copied wholesale. Velora is rebuilt clean, deliberately, in Laravel.

---

## 8. Amendment Rule

This file can change. But a change means:
1. Write down what's changing and why, right here, with a date.
2. It's a deliberate edit, not a workaround discovered while coding at 1am under deadline pressure.

---

## 9. The One Question That Matters

Before adding anything not already in FOUNDATION.md's Phase 1 scope, ask:

> "Does a real business, today, need this to run their operations on Velora — or am I building this because it seems important?"

If you can't answer with a specific business need, don't build it yet.
