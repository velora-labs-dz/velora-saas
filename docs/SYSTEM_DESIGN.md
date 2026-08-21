# VELORA — SYSTEM DESIGN

## 1. Purpose

This document defines the target system architecture from the first production MVP through the long-term Velora SaaS platform.

It is a design map. It does not mean every component is implemented today.

## 2. Target architecture

Velora begins as a modular monolith:

```text
Browser
  ↓
Laravel + Inertia
  ↓
Application layer
  ├── Identity
  ├── Organizations
  ├── Authorization
  ├── CRM
  ├── Catalog
  ├── Memberships
  ├── Scheduling
  ├── Attendance
  ├── Sales
  ├── Payments
  ├── Inventory
  ├── Operations
  ├── Notifications
  ├── Reporting
  └── SaaS Platform
  ↓
PostgreSQL
```

Later infrastructure:

```text
Laravel
 ├── PostgreSQL
 ├── Redis
 ├── Object Storage
 ├── Mail provider
 └── Payment provider(s)
```

The application remains one deployable product until scale creates a real reason to separate a subsystem.

## 3. Context boundaries

### Platform context

Owned by Velora Labs:

- plans
- subscriptions
- platform admins
- platform feature flags
- platform support
- platform metrics

### Organization context

Owned by each customer:

- employees
- clients
- services
- memberships
- appointments
- attendance
- sales
- payments
- inventory
- equipment
- operations
- reports

### Consumer context — future

Owned by Velora Marketplace:

- consumer accounts
- discovery
- saved businesses
- bookings
- reviews
- marketplace notifications

The marketplace must consume SaaS business truth rather than create duplicate business records.

## 4. Request lifecycle

Expected mutation flow:

```text
HTTP request
 → authentication
 → current organization resolution
 → request validation
 → authorization policy
 → action/service
 → database transaction where required
 → domain events/audit
 → response
```

## 5. Organization context

The current organization is a server-side context.

A user may belong to several organizations.

The active organization is verified against organization membership.

All organization-owned actions require a valid organization context.

## 6. Domain structure

Recommended logical application areas:

```text
app/
  Domain/
    Identity/
    Organizations/
    CRM/
    Catalog/
    Memberships/
    Scheduling/
    Attendance/
    Sales/
    Payments/
    Inventory/
    Operations/
    Notifications/
    Reporting/
    Platform/
  Actions/
  Policies/
  Http/
```

The exact filesystem can evolve; the domain boundaries cannot silently disappear.

## 7. Transactions

Transactions are mandatory for workflows where several records must change together.

Examples:

- completing a sale
- receiving a payment and issuing an invoice
- membership renewal
- stock adjustment tied to a sale
- organization creation + owner membership

## 8. Events/jobs

Use events when they remove coupling.

Use queued jobs for work such as:

- email
- exports
- reminders
- heavy reports
- imports
- integrations

Do not introduce event-driven complexity merely for style.

## 9. Caching

Redis is a future infrastructure component.

Use it when there is an actual requirement for:

- query caching
- queues
- rate limiting
- distributed locks

PostgreSQL remains the source of truth.

## 10. Storage

Sensitive files eventually live in private object storage.

Objects are namespaced by organization:

```text
organizations/{organization_id}/...
```

Access is authorized server-side and/or through controlled signed URLs.

## 11. Reporting architecture

Small lists may be queried normally.

Large analytics must use database aggregation, reporting queries, precomputed aggregates or asynchronous reports where necessary.

The browser must not load entire operational tables simply to calculate totals.

## 12. Long-term evolution

The architecture leaves room for:

- public API
- integrations
- mobile apps
- marketplace
- device adapters
- advanced analytics
- AI

These are future boundaries, not current build requirements.
