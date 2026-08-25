# VELORA — TARGET DATABASE SCHEMA

Status: Target design blueprint. Not all tables are to be created during Phase 1.

This document exists specifically to prevent the product from silently shrinking into a shallow CRUD design.

## 1. Database standards

- PostgreSQL
- snake_case
- singular model names / plural table names according to Laravel conventions
- foreign keys
- explicit indexes
- explicit constraints
- timestamps
- ULID/UUID strategy decided before public identifiers are exposed
- monetary values use DECIMAL or integer minor units, never floating point
- stable machine-readable enum/state values
- organization-owned tables carry `organization_id`

## 2. Foundation tables

### users

Target fields:

- id
- name
- email
- email_verified_at
- password
- remember_token
- created_at
- updated_at

Future:

- phone
- locale
- timezone
- status
- last_login_at

### organizations

Target fields:

- id
- name
- slug
- legal_name
- timezone
- locale
- currency
- status
- contact_email
- contact_phone
- address_line_1
- address_line_2
- city
- wilaya
- postal_code
- country_code
- created_by
- created_at
- updated_at

Do not add every legal/business field until the business workflow requires them.

### organization_members

Target fields:

- id
- organization_id
- user_id
- role
- is_active
- joined_at
- created_at
- updated_at

Constraints:

- unique organization_id + user_id
- role must be valid
- user must not be duplicated in an organization

### roles

If custom DB-backed roles are required, fields:

- id
- organization_id nullable only if global templates are deliberately supported
- name
- slug
- description
- is_system
- created_at
- updated_at

Phase 1 may implement fixed roles without custom role records.

### permissions

Target fields:

- id
- key
- name
- description
- module
- action
- created_at
- updated_at

### role_permissions

Target fields:

- role_id
- permission_id

## 3. CRM

### clients

Target fields:

- id
- organization_id
- first_name
- last_name
- display_name if required
- email
- phone
- alternate_phone
- date_of_birth nullable
- gender nullable
- status
- notes
- tags JSON/related table depending on final design
- created_by
- created_at
- updated_at
- deleted_at where soft deletion is justified

Future:

- address
- emergency_contact
- preferred_contact_method
- marketing_consent
- source
- external_reference

### client_notes — future

- id
- organization_id
- client_id
- author_id
- body
- created_at
- updated_at

### client_documents — future

- id
- organization_id
- client_id
- uploaded_by
- disk
- path
- original_name
- mime_type
- size_bytes
- created_at

## 4. Catalog

### services

- id
- organization_id
- name
- description
- duration_minutes
- price
- currency
- capacity
- status
- created_by
- created_at
- updated_at

### service_categories — future

- id
- organization_id
- name
- description
- sort_order
- active
- timestamps

### service_employee — future relation

- service_id
- employee_id

## 5. Employees

### employees

Target fields:

- id
- organization_id
- user_id nullable
- first_name
- last_name
- email
- phone
- job_title
- employment_status
- hire_date
- notes
- created_at
- updated_at

The `user_id` relationship is optional because not every employee must necessarily have a login.

## 6. Memberships

### membership_plans

- id
- organization_id
- name
- description
- duration_value
- duration_unit
- price
- currency
- sessions_limit nullable
- visits_per_period nullable
- freeze_allowed
- freeze_limit nullable
- active
- created_at
- updated_at

### memberships

- id
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
- activated_at
- frozen_at nullable
- cancelled_at nullable
- cancellation_reason nullable
- notes
- created_by
- created_at
- updated_at

## 7. Scheduling

### appointments

- id
- organization_id
- client_id
- service_id
- employee_id
- location_id nullable until location module is active
- starts_at
- ends_at
- status
- booking_channel
- notes
- cancellation_reason nullable
- created_by
- created_at
- updated_at

Future:

- resource_id
- deposit_amount
- recurrence_id
- marketplace_booking_id
- external_calendar_event_id

### staff_availability — future

- id
- organization_id
- employee_id
- weekday
- starts_at
- ends_at
- is_available
- created_at
- updated_at

### appointment_exceptions — future

For holidays, leave, blocked times, etc.

## 8. Attendance

### attendance

- id
- organization_id
- client_id
- check_in_at
- check_out_at nullable
- source
- notes
- recorded_by
- created_at
- updated_at

Future:

- device_id
- external_event_id
- location_id

## 9. Payments

### payments

- id
- organization_id
- client_id
- membership_id nullable — see ADR-010; a payment may or may not be tied
  to a specific membership. When it is, `Membership.paid_amount`/
  `remaining_amount` are kept in sync (recorded on payment, reversed on
  void/refund).
- amount
- currency
- method — cash/transfer only in Phase 1, no gateway
- status — `recorded` / `voided` / `refunded`
- reference nullable
- paid_at
- refunded_amount — cumulative amount refunded so far (0 unless
  status=refunded); moved out of "Future" per ADR-010
- refund_reason nullable
- voided_at nullable
- void_reason nullable
- notes
- recorded_by
- created_at
- updated_at

Future:

- payment_intent_id
- provider
- provider_transaction_id
- parent_payment_id

## 10. Locations — future Phase 2

### locations

- id
- organization_id
- name
- code
- timezone
- address_line_1
- address_line_2
- city
- wilaya
- postal_code
- phone
- email
- is_active
- created_at
- updated_at

Then appropriate operational tables receive `location_id`.

## 11. Sales / POS — future

### products

- id
- organization_id
- category_id
- name
- sku
- barcode
- description
- cost_price
- sale_price
- currency
- track_stock
- stock_minimum
- active
- created_at
- updated_at

### sales

- id
- organization_id
- client_id nullable
- employee_id nullable
- location_id nullable
- subtotal
- discount_amount
- tax_amount
- total
- currency
- status
- paid_at
- created_by
- created_at
- updated_at

### sale_items

- id
- sale_id
- product_id nullable
- service_id nullable
- description_snapshot
- quantity
- unit_price
- discount_amount
- tax_amount
- total
- created_at
- updated_at

## 12. Inventory — future

### inventory_movements

- id
- organization_id
- product_id
- location_id/warehouse_id
- type
- quantity
- unit_cost
- reference_type
- reference_id
- reason
- performed_by
- created_at

### suppliers

- id
- organization_id
- name
- email
- phone
- address
- notes
- created_at
- updated_at

### purchases

- id
- organization_id
- supplier_id
- location_id
- status
- subtotal
- tax_amount
- total
- currency
- purchased_at
- created_by
- created_at
- updated_at

### purchase_items

- id
- purchase_id
- product_id
- quantity
- unit_cost
- total
- created_at
- updated_at

## 13. Invoicing — future

### invoices

- id
- organization_id
- client_id
- number
- status
- issue_date
- due_date
- subtotal
- tax_amount
- discount_amount
- total
- currency
- paid_amount
- notes
- created_by
- created_at
- updated_at

### invoice_items

- id
- invoice_id
- description
- product_id nullable
- service_id nullable
- quantity
- unit_price
- tax_amount
- discount_amount
- total
- created_at
- updated_at

## 14. Expenses — future

### expenses

- id
- organization_id
- category_id
- supplier_id nullable
- amount
- currency
- expense_date
- payment_method
- status
- description
- attachment_path nullable
- recorded_by
- created_at
- updated_at

## 15. Equipment — future

### equipment

- id
- organization_id
- location_id nullable
- name
- category
- serial_number
- supplier_id nullable
- purchase_date
- purchase_cost
- currency
- warranty_until nullable
- status
- notes
- created_at
- updated_at

## 16. Maintenance — future

### maintenance_plans

- id
- organization_id
- equipment_id
- frequency
- next_due_at
- assigned_to nullable
- active
- created_at
- updated_at

### maintenance_work_orders

- id
- organization_id
- equipment_id
- plan_id nullable
- type
- status
- priority
- scheduled_at
- completed_at nullable
- assigned_to nullable
- description
- cost
- currency
- created_at
- updated_at

## 17. Notifications — future

### notifications

- id
- organization_id nullable
- user_id nullable
- client_id nullable
- type
- channel
- status
- payload JSON
- sent_at nullable
- read_at nullable
- created_at
- updated_at

## 18. Audit — future / earlier if required by production

### audit_events

- id
- organization_id nullable
- actor_user_id nullable
- action
- subject_type
- subject_id
- event_data JSON
- ip_address nullable
- user_agent nullable
- created_at

Financial and security-relevant actions should be auditable.

## 19. SaaS platform billing — future

### plans

- id
- code
- name
- description
- billing_interval
- price
- currency
- active
- created_at
- updated_at

### plan_features

- id
- plan_id
- feature_key
- limit_value nullable
- enabled
- created_at
- updated_at

### subscriptions

- id
- organization_id
- plan_id
- status
- starts_at
- trial_ends_at nullable
- current_period_starts_at
- current_period_ends_at
- cancelled_at nullable
- provider nullable
- provider_subscription_id nullable
- created_at
- updated_at

### subscription_events

- id
- subscription_id
- type
- provider_event_id nullable
- payload JSON
- occurred_at
- created_at

## 20. Marketplace — future

### consumer_profiles

- id
- user_id
- display_name
- phone
- locale
- timezone
- created_at
- updated_at

### organization_listings

- id
- organization_id
- public_name
- description
- cover_image
- listing_status
- visibility
- created_at
- updated_at

### marketplace_bookings

- id
- consumer_user_id
- organization_id
- service_id
- appointment_id
- status
- source
- created_at
- updated_at

### reviews

- id
- organization_id
- consumer_user_id
- marketplace_booking_id
- rating
- body
- status
- created_at
- updated_at

## 21. Required indexes

At minimum, high-volume tenant tables should receive indexes such as:

- `(organization_id)`
- `(organization_id, created_at)`
- `(organization_id, status)`
- `(organization_id, client_id)`
- `(organization_id, starts_at)`

Only add indexes based on actual access patterns and query plans.

## 22. Schema rule

This document describes the destination.

`PROGRESS.md` describes what currently exists.

Never confuse a planned field with a deployed field.
