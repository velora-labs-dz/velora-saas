# VELORA — DOMAIN MODEL

## 1. Modeling philosophy

Velora models real business concepts rather than forms.

A field exists because a business rule, relationship, workflow, or audit requirement needs it.

## 2. Core identity

### User

A human authenticated to Velora.

Responsibilities:

- authentication identity
- personal account information
- system sessions

A User does NOT globally belong to one organization.

### Organization

A customer business using Velora.

Important attributes:

- legal/display name
- slug
- timezone
- locale
- currency
- status
- business type(s)
- contact information
- created_by

### OrganizationMember

The relationship between User and Organization.

Contains:

- organization
- user
- role
- status
- joined_at

This is the authorization boundary.

### Employee

A business's staff profile.

An Employee is operationally distinct from an authenticated User, although a future implementation may link the two.

## 3. CRM

### Client

A customer of an Organization.

A Client is not a User and is not an OrganizationMember.

A Client may have:

- profile information
- contact information
- notes
- tags
- status
- memberships
- appointments
- attendance
- payments
- purchases

### Prospect — future

A lead who has not yet become a Client.

Do not create it until the workflow requires it.

## 4. Services

### Service

A service offered by an Organization.

Target attributes:

- organization_id
- category_id if categories are implemented
- name
- description
- duration_minutes
- price
- currency
- capacity
- active flag
- tax configuration where needed
- created_by
- timestamps

Staff/resource relationships should be separate relations, not comma-separated IDs.

## 5. Memberships

### MembershipPlan

What the Organization sells.

Target attributes:

- organization_id
- name
- description
- duration/unit
- price
- currency
- visit/session limits where applicable
- freeze rules
- active
- timestamps

### Membership

A Client's actual purchase/assignment of a plan.

Target attributes:

- organization_id
- client_id
- membership_plan_id
- status
- starts_at
- ends_at
- price
- currency
- paid_amount
- remaining_amount
- notes
- created_by
- activated_at
- frozen_at
- cancelled_at
- timestamps

State must be modeled explicitly.

## 6. Scheduling

### Appointment

A scheduled service interaction.

Target attributes:

- organization_id
- client_id
- service_id
- employee_id
- location_id when locations become active
- starts_at
- ends_at
- status
- booking_channel
- notes
- cancellation_reason
- created_by
- timestamps

Future fields such as resource_id, deposit/payment relation, recurrence_id and marketplace_source belong to later phases.

## 7. Attendance

### Attendance

A recorded presence event.

Target attributes:

- organization_id
- client_id
- check_in_at
- check_out_at
- source
- notes
- recorded_by
- timestamps

Future source values may include:

- manual
- qr
- barcode
- rfid
- device
- marketplace

## 8. Payments

### Payment

A customer-to-organization financial event.

Target attributes:

- organization_id
- client_id
- amount
- currency
- method
- status
- reference
- paid_at
- notes
- recorded_by
- timestamps

The payment model must not be confused with Velora's own SaaS subscription billing.

## 9. Future financial entities

Later:

- Invoice
- InvoiceItem
- Refund
- CreditNote
- Expense
- CashSession
- FinancialAdjustment

These must be introduced only when the corresponding workflows enter scope.

## 10. State philosophy

States represent business meaning, not UI styling.

Example membership lifecycle:

```text
draft
  ↓
active
  ├── frozen
  │     ↓
  │   active
  ├── cancelled
  └── expired
```

The exact allowed transitions must be tested.

## 11. Cross-domain rule

Every relation between domains must have a reason.

Example:

```text
Client
 ↓
Appointment
 ↓
Service
```

A Client does not directly contain a copy of the Service's price.

The appointment records the information required to preserve historical truth when appropriate.
