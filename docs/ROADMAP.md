# VELORA — MASTER DEVELOPMENT ROADMAP

This is the complete ordered development map.

The phases are intentionally broader than the current MVP. They exist so the project never loses the destination.

## Phase 0 — Project Foundation

### Goal

Create the engineering foundation.

### Deliverables

- GitHub organization
- repository
- Laravel application
- Inertia + React + TypeScript
- PostgreSQL
- Pest
- environment configuration
- CI baseline
- documentation system
- coding conventions
- domain terminology
- backup policy definition

### Exit criteria

- clean local install
- app boots
- test suite boots
- GitHub repo is healthy
- documentation exists

---

# Phase 1 — MVP / Core Operations

This is the first commercially meaningful slice.

## Step 1 — Identity + Auth

Features:

- registration
- login
- logout
- password reset
- email verification when ready
- session handling

## Step 2 — Organizations + Tenancy

Features:

- create organization
- organization membership
- organization switch
- current organization context
- tenant scoping
- isolation tests

## Step 3 — RBAC

Features:

- Owner
- Admin
- Staff
- Viewer
- policies
- permission matrix
- backend enforcement
- security tests

## Step 4 — Clients

Features:

- client creation
- profile
- search
- list
- update
- archive/delete according to policy
- basic notes

## Step 5 — Services + Memberships

Features:

- services
- membership plans
- memberships
- membership state
- assignment
- basic renewal

## Step 6 — Appointments

Features:

- create
- edit
- cancel
- calendar
- client
- service
- staff
- time range
- conflict validation

## Step 7 — Attendance

Features:

- check-in
- check-out
- manual history

## Step 8 — Payments

Features:

- cash
- transfer
- payment record
- balance tracking

### Phase 1 exit criteria

A real service business can:

1. create an account
2. create its organization
3. invite staff
4. create clients
5. define services
6. create membership plans
7. assign memberships
8. book appointments
9. record attendance
10. record payments

and organization isolation and role enforcement tests pass.

---

# Phase 2 — Operational Depth

Only begin after Phase 1 is used by a real business.

## 2.1 Locations

- locations
- location assignment
- location filters
- location-aware appointments
- location-aware attendance
- location-aware reporting

## 2.2 Staff

- employee profiles
- staff schedules
- availability
- time off
- service qualifications

## 2.3 Membership depth

- renewals
- freeze
- expiry automation
- usage limits
- session packages
- member history

## 2.4 Scheduling depth

- recurring appointments
- staff availability
- resource availability
- no-show workflow
- deposits

## 2.5 CRM depth

- notes
- documents
- tags
- activities
- communication history

---

# Phase 3 — Commerce

## Sales

- products
- POS
- cart
- discounts
- taxes
- receipts

## Payments

- multiple payment records
- split payments if needed
- refunds
- adjustments

## Inventory

- stock
- stock movements
- suppliers
- purchasing
- stock adjustments
- stock transfers
- inventory locations

## Financial integrity

- atomic checkout
- financial auditability
- transaction reconciliation

---

# Phase 4 — Business Administration

## Invoicing

- invoices
- invoice items
- numbering
- PDFs
- credit notes
- payment relations

## Expenses

- expense records
- categories
- suppliers
- recurring expenses

## Reports

- revenue
- clients
- memberships
- attendance
- sales
- inventory
- expenses
- staff performance

## Exports

- CSV
- XLSX
- PDF where useful

---

# Phase 5 — Operations

## Equipment

- registry
- serial numbers
- locations
- warranty

## Maintenance

- schedules
- work orders
- preventive maintenance
- corrective maintenance
- costs

## Facilities

- spaces
- room availability
- cleaning
- laundry where appropriate
- incidents

---

# Phase 6 — Communication & Automation

## Notifications

- in-app
- email

Later:

- SMS
- WhatsApp Business

## Automation

- membership reminders
- appointment reminders
- inactive client workflows
- low-stock alerts
- payment reminders
- operational reminders

## Templates

- message templates
- email templates
- notification preferences

---

# Phase 7 — SaaS Commercial Platform

## Plans

- Free/trial if commercially useful
- Starter
- Growth/Professional
- Enterprise

## Subscription

- trial
- active
- past_due
- grace
- cancelled
- expired

## Billing

- provider integration
- payment events
- webhooks
- invoices
- refunds
- plan changes

## Entitlements

- feature flags
- usage limits
- user limits
- location limits
- module limits

## Platform administration

- organization management
- subscriptions
- billing
- support
- platform analytics

---

# Phase 8 — Customer Experience

## Client portal

- login
- membership
- appointments
- payments
- invoices
- attendance
- notifications

## Customer communication

- reminders
- confirmations
- campaigns
- loyalty

## Mobile

Only when mobile workflows justify a dedicated client/staff application.

---

# Phase 9 — Integrations & Device Ecosystem

## APIs

- public API
- authentication
- scopes
- versioning
- rate limits

## Webhooks

- appointment events
- payment events
- membership events
- customer events

## Devices

- QR
- RFID
- access control
- ZKTeco adapters
- turnstiles

Use adapter interfaces so the domain is not tied to one manufacturer.

---

# Phase 10 — Intelligence

## Analytics

- cohort analysis
- retention
- utilization
- revenue forecasting
- customer lifecycle

## Automation intelligence

- churn signals
- no-show risk
- scheduling suggestions

## AI

- business assistant
- report explanations
- operational recommendations
- campaign drafting

AI is built on reliable operational data.

---

# Phase 11 — Velora Marketplace

## Consumer account

- profile
- location
- favorites
- preferences

## Discovery

- search
- category
- location
- distance
- availability
- price
- rating

## Business listing

- public profile
- services
- schedules
- photos
- reviews

## Booking

- availability
- appointment
- confirmation
- cancellation
- payment

## Reviews

- verified booking
- rating
- moderation

## Marketplace analytics

- discovery
- conversion
- bookings
- revenue

---

# Phase 12 — Velora Platform / Ecosystem

Long-term capabilities:

- payment infrastructure
- developer platform
- integrations ecosystem
- marketplace tools
- partner portal
- advanced analytics
- AI platform

This is the long-term destination, not today's backlog.

---

# Phase priority rule

Never jump directly from Phase 1 to Phase 11 because a competitor has a feature.

Each phase requires evidence from:

- customers
- product usage
- business economics
- technical readiness
