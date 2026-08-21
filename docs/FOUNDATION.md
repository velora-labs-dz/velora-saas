# FOUNDATION.md
### Velora SaaS — Practical Build Doc
Status: Living document. Update as decisions change. This is not the constitution — see VELORA_SOURCE_OF_TRUTH.md for the rules that don't change.

---

## 1. What Velora Is

A multi-tenant SaaS platform for service businesses (gyms, spas, beauty institutes — Algeria first) to manage clients, memberships, appointments, attendance, and payments in one system, instead of spreadsheets + WhatsApp + a notebook.

It is not yet: a marketplace, a booking discovery app, or a multi-industry ERP. It is one thing done correctly for one category of business.

---

## 2. Stack (locked — do not revisit without a real reason)

| Layer | Choice |
|---|---|
| Backend | Laravel 13 |
| Frontend | Inertia + React + TypeScript |
| Database | PostgreSQL |
| Cache/Queues | Redis |
| Local env | Laravel Herd (Windows) + Docker for Postgres/Redis |
| Testing | Pest |

No microservices. No separate API + SPA split. No billing provider abstraction, no mail provider abstraction, no storage abstraction — until there's an actual second provider to abstract for. Abstracting around a decision you haven't made yet is waste.

---

## 3. MVP Entities (and only these)

```
User
Organization
OrganizationMember   (user ↔ organization, with role)
Role
Permission
Client
Service
MembershipPlan
Membership            (client's active plan)
Appointment
Attendance
Payment
```

That's ~11 tables. Not 40. Everything else (inventory, equipment, invoicing, notifications, reporting, marketplace) is Phase 2+ and does not get a table until Phase 1 is working end-to-end for one real business.

---

## 4. Phase 1 Scope (what "done" means before touching anything else)

- [ ] Auth (register, login, password reset) via Breeze
- [ ] Organization creation + membership
- [ ] Roles: Owner, Admin, Staff, Viewer (start with 4, not 5 — add Manager later if a real need appears)
- [ ] Client CRUD, scoped to organization
- [ ] Service CRUD, scoped to organization
- [ ] Membership plan + membership assignment
- [ ] Appointment booking (no recurrence, no resource conflicts yet — just client + service + staff + time slot)
- [ ] Manual attendance check-in/out
- [ ] Payment recording (cash/transfer only, no provider integration)
- [ ] Tenant isolation test suite passing (Org A cannot touch Org B, for every entity above)
- [ ] Role enforcement test suite passing (Viewer can't mutate, Staff can't manage org, etc.)

Nothing else ships before this list is fully green.

---

## 5. Explicitly OUT of scope right now

Do not build, discuss implementing, or create tables for any of these until Phase 1 is live with a real business using it:

- Marketplace (consumer side, discovery, booking network)
- Multi-vertical business-type configuration system
- Billing/subscription system for Velora itself (you're not charging yet)
- Payment provider integration (Stripe/CIB/local gateway — cash/manual for now)
- Notification system (email/SMS/WhatsApp abstraction)
- Inventory, equipment, maintenance
- Invoicing/PDF generation
- Public API
- Super Admin panel (you are the only admin right now — use Tinker/DB directly)
- Webhooks
- Audit log table (add when you have a real reason someone other than you needs to see history)

If you catch yourself building one of these, stop and ask: does Phase 1 need this to onboard one real business? If no, it goes on a `LATER.md` list, not into code.

---

## 6. Repo Structure

```
velora-saas/
├── app/
│   ├── Models/
│   ├── Policies/          ← one per major entity, from day 1
│   ├── Http/
│   │   ├── Controllers/   ← thin, delegate to Actions
│   │   └── Requests/      ← Form Request validation, always
│   └── Actions/           ← business logic lives here, not in controllers
├── database/
│   └── migrations/
├── resources/js/          ← React/TS
├── tests/
│   ├── Feature/
│   └── Security/          ← tenant isolation + role enforcement tests live here
├── docs/
│   ├── FOUNDATION.md               (this file)
│   └── VELORA_SOURCE_OF_TRUTH.md
└── README.md
```

---

## 7. Phase Order (do not skip ahead)

1. Identity + Auth
2. Organizations + Tenancy + Tenant isolation test
3. RBAC (roles/permissions) + Role enforcement test
4. Clients (CRM basics)
5. Services + Membership Plans
6. Appointments
7. Attendance
8. Payments (manual only)
9. → **First real business onboarded here.** Everything after this point is driven by what that business actually needs, not by this document.

---

## 8. Weekly Check-in (even solo)

Every Friday, answer honestly:
- What actually shipped this week?
- What did I build that wasn't on this list? Why?
- Is Phase 1 closer to done or did scope grow?

If scope grew without a real customer forcing it, that's the signal to stop and re-read section 5.
