# VELORA — SOURCE OF TRUTH

Status: Living constitution. Changes require an explicit amendment and reason.

## 1. Product identity

Velora is a B2B multi-tenant SaaS platform for service businesses, starting with Algeria and initially focusing on fitness, SPA, beauty and wellness operations.

Velora is intended to become a business operating platform, not a collection of unrelated CRUD screens.

Nexora and Style Le Club are prior work and research inputs. Velora is a clean rebuild in Laravel.

## 2. Core architectural rules

### 2.1 No table without a domain reason

Every persistent entity must represent a real business concept, a platform concern, or an operational requirement.

A UI page is never a sufficient reason for a table.

### 2.2 No business mutation without authorization

Every mutation is authorized on the server.

Frontend permissions are UX only.

### 2.3 Tenant ownership is mandatory

Every organization-owned record has an `organization_id`.

The server derives and verifies organization context.

Never trust a client-provided organization ID as proof of ownership.

### 2.4 Financial records are append-oriented

Payments, invoices and financial history are not casually edited in place.

Corrections use explicit operations such as refunds, reversals, credit notes or adjustments.

### 2.5 Tenant isolation is continuously tested

Organization A must never read, create, modify, delete or infer Organization B data.

### 2.6 Roles are organization-scoped

A user has a role through membership in an organization.

The global `users` record never owns the organization role.

### 2.7 Status values are domain values

Store stable machine values such as:

- `active`
- `frozen`
- `expired`
- `cancelled`

Never store translated display strings as state.

### 2.8 Controllers stay thin

The expected server flow is:

Request validation → authorization → application action → domain/business operation → response.

### 2.9 The product is not built from screens

For every feature, define:

- problem
- actor
- domain object
- state
- rules
- authorization
- persistence
- workflow
- tests
- UI

### 2.10 No client-specific forks

A customer request becomes a reusable configuration, feature, workflow, or product capability.

Do not build private forks for individual customers.

## 3. Current stack commitment

- Laravel 13
- Inertia
- React
- TypeScript
- PostgreSQL
- Pest
- Redis when queue/cache infrastructure is actually introduced
- Laravel Herd for local application runtime on Windows

No microservices at the current stage.

No separate Next.js frontend.

No separate API + SPA split unless a documented architecture decision changes this.

## 4. Current environment truth

At the current project stage:

- Laravel is running through Herd.
- PostgreSQL is installed/running natively on Windows.
- pgAdmin is being used for database administration.
- The `velora_saas` database exists.
- Default Laravel migrations have run.
- Docker has NOT been introduced.
- Redis has NOT been configured or used yet.
- The landing page is currently being built as a pixel-accurate recreation/reference of the current Nexora landing experience.
- After the landing work, the next backend milestone is Organizations + Tenancy.

Documentation must describe reality. Do not write that Docker or Redis is active until it actually is.

## 5. Initial roles

Phase 1 roles:

- Owner
- Admin
- Staff
- Viewer

Manager is deliberately deferred until a demonstrated business need exists.

## 6. Product principle

The MVP must be small in implementation scope but serious in architecture.

Small scope does not mean shallow domain modeling.

## 7. Amendment rule

Any change to these rules must record:

- date
- changed rule
- reason
- consequences

in `DECISIONS.md`.
