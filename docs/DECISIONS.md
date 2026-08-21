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
