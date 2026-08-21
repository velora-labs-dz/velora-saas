# VELORA — SAAS BILLING

## Status

Future commercial phase. Not part of Phase 1.

## 1. Separate concepts

### Customer payment

Client → Organization

### SaaS subscription payment

Organization → Velora Labs

Never mix these.

## 2. Target lifecycle

```text
trialing
  ↓
active
  ↓
past_due
  ↓
grace_period
  ↓
cancelled / expired
```

## 3. Target entities

- plans
- plan_features
- subscriptions
- subscription_events
- billing_transactions
- refunds
- platform_invoices

Do not implement the entire billing system before there is a real commercial requirement.

## 4. Entitlements

Plans control:

- modules
- users
- locations
- usage
- advanced functionality

The customer must not be able to grant itself a paid feature.

## 5. Provider strategy

Select payment providers based on:

- Algeria support
- business registration requirements
- settlement
- fees
- recurring support
- webhook reliability
- international expansion needs

Provider selection is a business decision, not merely a technical abstraction problem.

## 6. Billing invariants

A subscription event must be idempotent.

A payment webhook must not create duplicate financial events.

Plan changes must not silently corrupt billing periods.

## 7. Commercial metrics

Eventually track:

- MRR
- ARR
- ARPU
- churn
- trial conversion
- active organizations
- payment failure rate
