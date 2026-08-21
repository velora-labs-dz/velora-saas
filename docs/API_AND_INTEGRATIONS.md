# VELORA — API & INTEGRATIONS

## 1. Current state

Velora does not need a public API in Phase 1.

Internal Laravel application calls are the first integration boundary.

## 2. Future public API

When justified:

```text
/api/v1/
```

Principles:

- versioning
- authentication
- permission scopes
- organization scoping
- rate limiting
- idempotency
- consistent errors
- auditability

## 3. API resources

Likely future resources:

- organizations
- clients
- employees
- services
- memberships
- appointments
- attendance
- products
- sales
- payments

## 4. Webhooks

Future events may include:

- client.created
- membership.activated
- membership.expired
- appointment.created
- appointment.cancelled
- payment.received
- sale.completed

## 5. Hardware integrations

Use adapter interfaces.

Core domain:

```text
AccessDevice
```

Adapters:

- QR reader
- RFID
- ZKTeco
- turnstile
- other devices

The business domain must not depend directly on one vendor.

## 6. Payment integrations

Future provider abstraction:

```text
PaymentGateway
```

Provider implementations can later include suitable Algerian or international providers.

The domain stores provider-independent payment concepts.

## 7. Communication providers

Future abstraction:

```text
NotificationChannel
```

Providers:

- email
- SMS
- WhatsApp Business
- push

Do not introduce the abstraction until the first real provider/use case justifies it, but keep domain events provider-independent.
