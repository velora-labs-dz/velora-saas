# VELORA — CLAUDE HANDOFF / CONTEXT

## How to use this repository

You are not starting a new product from zero.

You are continuing an existing implementation.

Before writing code:

1. read `VELORA_SOURCE_OF_TRUTH.md`
2. read `SYSTEM_DESIGN.md`
3. read `DOMAIN_MODEL.md`
4. read `DATABASE_SCHEMA.md`
5. read `ROADMAP.md`
6. read `SECURITY.md`
7. read `TESTING.md`
8. read `PROGRESS.md`
9. inspect the actual repository
10. compare documentation to code before making claims

## Critical distinction

Design documents describe intended future architecture.

`PROGRESS.md` describes what is actually implemented.

If the two conflict, inspect the repository and then update `PROGRESS.md`.

## Do not

- invent features
- invent database tables
- shrink the long-term product to CRUD
- build future phases early
- rewrite working code without reason
- trust frontend authorization
- trust client-supplied organization IDs
- mark untested code as complete
- say Redis/Docker is configured when it is not
- copy Nexora's database wholesale

## Current task

The current next technical milestone is:

# Organizations + Tenancy

The landing page is currently the only active presentation work.

After landing-page completion, return to the tenancy milestone.

## Required implementation sequence

```text
organizations
 ↓
organization_members
 ↓
relationships
 ↓
current organization resolver/context
 ↓
authorization
 ↓
tenant isolation tests
```

Do not build clients before tenancy isolation passes.

## If uncertain

Use this priority:

1. Source of Truth
2. System Design
3. Domain Model
4. Database Schema
5. Roadmap
6. Security
7. Testing
8. Progress
9. actual repository/code
10. general engineering judgment

When a decision changes architecture, record it in `DECISIONS.md`.

## Required end-of-session update

Update `PROGRESS.md` with:

- what changed
- what was tested
- exact next task
- failures/blockers
- database changes
- security impact
