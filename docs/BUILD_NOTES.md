# VELORA — BUILD NOTES

## Current implementation philosophy

The product is intentionally built in vertical slices.

A vertical slice means:

```text
domain concept
 → migration
 → model
 → relationships
 → authorization
 → request validation
 → action/business logic
 → controller
 → UI
 → tests
 → documentation
```

The exact files may differ by Laravel conventions, but the completeness model must not.

## Why the target schema is bigger than Phase 1

The project has a long-term target system.

The database blueprint intentionally records future entities and fields so the project does not lose architectural direction.

However:

> A planned table is not a current table.

Never create future tables just because they appear in `DATABASE_SCHEMA.md`.

Open a roadmap phase first.

## Preventing CRUD drift

Before building a CRUD page, write the workflow.

Example:

### Membership creation

1. select client
2. select membership plan
3. validate dates
4. calculate price
5. determine status
6. create membership
7. optionally record payment
8. produce audit/event if required
9. show resulting state

The screen is an interface to the workflow, not the workflow itself.

## Preventing rewrite drift

If a later phase needs a field that was not present initially:

1. verify why it is needed
2. update the domain model
3. update the target schema
4. create a migration
5. update tests
6. update the relevant roadmap phase
7. record the decision if architectural

Do not silently modify the original intent.

## Preventing overengineering

A future concern becomes code only when:

- the current phase requires it,
- a customer requires it,
- or a system constraint requires it.

Until then, keep it documented in design/LATER.
