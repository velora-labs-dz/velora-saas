# Velora Labs Documentation System

This directory is the engineering memory of Velora.

## Purpose

These documents exist so that a new Claude/engineer can open a fresh conversation with:

1. the latest repository ZIP
2. these Markdown files
3. the current `PROGRESS.md`

and continue development without inventing architecture, changing terminology, or silently shrinking the product into a CRUD application.

## Source-of-truth hierarchy

1. `VELORA_SOURCE_OF_TRUTH.md` — permanent principles and non-negotiable architectural rules.
2. `SYSTEM_DESIGN.md` — target architecture and system boundaries.
3. `DOMAIN_MODEL.md` — business concepts and their rules.
4. `DATABASE_SCHEMA.md` — target data model and planned fields.
5. `ROADMAP.md` — ordered implementation phases.
6. `SECURITY.md` — security requirements.
7. `TESTING.md` — verification requirements.
8. `UX_DESIGN.md` — product experience rules.
9. `API_AND_INTEGRATIONS.md` — external/system interfaces.
10. `BILLING.md` — future SaaS subscription architecture.
11. `MARKETPLACE.md` — future marketplace architecture.
12. `DEVOPS.md` — environments, deployment, backup, recovery.
13. `GITHUB_WORKFLOW.md` — source control and engineering workflow.
14. `DECISIONS.md` — deliberate architectural decisions.
15. `LATER.md` — intentionally deferred ideas.
16. `PROGRESS.md` — what is actually implemented today.
17. `Localization.md` — Localization Specification.

## Important

A document describing a future phase is a design commitment, not permission to build that phase early.

`ROADMAP.md` defines the destination. `PROGRESS.md` defines reality.

Never claim a feature exists because it is described in a design document.
