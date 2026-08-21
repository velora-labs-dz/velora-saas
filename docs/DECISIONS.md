# VELORA — ARCHITECTURAL DECISIONS

This is the deliberate decision record.

A decision belongs here only when it changes architecture, domain modeling, infrastructure, or long-term product direction.

## ADR-001 — Laravel + Inertia + React

**Date:** 2026-08-21

**Decision:** Use Laravel 13 + Inertia + React + TypeScript.

**Reason:** This is an intentional rebuild rather than continuing the prior NestJS/Next.js stack. The stack is locked unless a real technical problem justifies reconsideration.

## ADR-002 — PostgreSQL

**Decision:** PostgreSQL is the primary relational database.

**Reason:** Strong relational integrity, constraints, transactions and suitability for multi-tenant business software.

## ADR-003 — Modular monolith

**Decision:** Start as a modular Laravel monolith.

**Reason:** A small team does not need distributed systems complexity. Domain boundaries are preserved internally so extraction remains possible later.

## ADR-004 — Organization-scoped roles

**Decision:** Roles belong to organization membership.

**Reason:** One user may work in several organizations with different responsibilities.

## ADR-005 — Backend authorization

**Decision:** Policies and server-side authorization are mandatory.

**Reason:** Frontend controls are not a security boundary.

## ADR-006 — Phase-first development

**Decision:** Complete coherent vertical slices before expanding breadth.

**Reason:** Prevents recreating Nexora as a broad but weak prototype.

## ADR-007 — Current local environment

**Decision:** Use Laravel Herd + native PostgreSQL on Windows for now.

**Decision not made:** Docker and Redis adoption remain future infrastructure decisions based on actual need.

## ADR-008 — Landing page

**Decision:** Build the landing page as a pixel-accurate reference/recreation of the current Nexora landing experience before returning to Organizations + Tenancy.

**Reason:** The landing page is an independent presentation task and does not alter the backend architecture.

## ADR-009 — Organization public identifier is the slug, not a new PK strategy

**Date:** 2026-08-21

**Decision:** `organizations.id` remains a standard auto-incrementing bigint. The slug (already a required field in `DATABASE_SCHEMA.md`) is used as the route/public-facing identifier (`getRouteKeyName()` returns `slug`; URLs are `/organizations/{slug}`), and the numeric id is never exposed in a route.

**Reason:** `DATABASE_SCHEMA.md` §1 flags "ULID/UUID strategy decided before public identifiers are exposed" as an open question. Introducing ULIDs/UUIDs now would be a real PK-type change across the schema with no immediate need — the slug already satisfies the actual requirement (don't expose a guessable sequential id) without that migration. If a future requirement needs globally-unique, sortable, or externally-generated identifiers (e.g. public API resource ids), ULID/UUID adoption should be revisited as its own ADR at that time.

**Consequences:** Route-model binding uses slug everywhere for `Organization`. Internal foreign keys (`organization_id` on child tables) continue to reference the bigint id as normal — this decision only affects what's exposed in URLs, not the internal schema.

