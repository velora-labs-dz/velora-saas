# Nexora — Localization Specification

**Document:** `Localization.md`  
**Status:** Foundational  
**Applies to:** Entire Nexora platform  
**Supported Locales:** Arabic (`ar`), French (`fr`), English (`en`)  
**Default Market:** Algeria  
**Primary Direction:** RTL for Arabic, LTR for French and English

---

## 1. Purpose

Nexora must be designed as a multilingual product from the beginning.

Localization is not a future feature and must not be implemented by translating the interface after the product is complete.

The application architecture, frontend, backend, database, emails, notifications, validation, formatting, URLs, metadata, and user preferences must all be capable of supporting multiple locales.

The initial supported locales are:

- `ar` — Arabic
- `fr` — French
- `en` — English

Arabic must be treated as a first-class RTL experience rather than as a translated LTR interface.

---

## 2. Localization Goals

Nexora localization must provide:

1. Consistent translations across the entire application.
2. Complete Arabic RTL support.
3. French support appropriate for the Algerian market.
4. English support for developers, international users, documentation, and future expansion.
5. Locale-aware formatting.
6. User-controlled language preferences.
7. Organization-level language defaults.
8. Browser/device language detection.
9. Localized system messages.
10. Localized transactional emails and notifications.
11. A structure that allows new languages to be added without architectural changes.

---

## 3. Supported Locales

### 3.1 Arabic

**Code:** `ar`

**Direction:** RTL

Arabic is one of Nexora's primary product languages.

The Arabic interface must not be implemented as a simple translation layer over an LTR UI.

RTL behavior must be explicitly tested throughout the product.

---

### 3.2 French

**Code:** `fr`

**Direction:** LTR

French is a primary language for the Algerian market.

French terminology must remain consistent across:

- Navigation
- Features
- Forms
- Billing
- Notifications
- Errors
- Documentation
- Emails

---

### 3.3 English

**Code:** `en`

**Direction:** LTR

English is supported from the beginning for:

- International users
- Developers
- Technical documentation
- Open-source contributors
- International agencies
- Future geographic expansion
- Product integrations

English support does not require perfect translation coverage on day one, but the architecture must support it from the first implementation.

---

## 4. Locale Priority

Initial product priority:

```text
ar → Primary local language
fr → Primary Algerian business language
en → International / technical language
```

This priority does not mean that Arabic must replace French or English.

All three locales are official supported Nexora locales.

---

## 5. Locale Resolution

When a user accesses Nexora for the first time, locale resolution should follow this order:

```text
1. Explicit user preference
        ↓
2. Organization preference
        ↓
3. Previously stored browser/device preference
        ↓
4. Browser Accept-Language
        ↓
5. Nexora default locale
```

The final fallback must be deterministic.

For the initial Algeria-focused deployment:

```text
Default locale = fr
```

Arabic must always remain immediately available.

The exact default may be changed later based on product analytics.

---

## 6. User Language Preference

Every authenticated user should have a locale preference.

Conceptually:

```text
User
 └── locale
      ├── ar
      ├── fr
      └── en
```

The preference must be persisted server-side so the user's language follows them across devices.

The frontend may cache the selected locale for performance, but the backend remains the authoritative source for authenticated users.

---

## 7. Organization Language Preference

Organizations may define a default language for their workspace.

Conceptually:

```text
Organization
 └── defaultLocale
```

This determines the initial language for users who do not yet have an explicit personal preference.

User preference takes precedence over organization preference.

---

## 8. Translation Architecture

All user-facing application text must be externalized.

Do not hardcode user-facing strings inside components, controllers, services, or templates.

Bad:

```tsx
<button>Créer une organisation</button>
```

Correct:

```tsx
<Button>
  {t("organization.create")}
</Button>
```

Translation keys must be stable semantic identifiers.

Recommended structure:

```text
organization.create
organization.delete
organization.settings
```

Avoid keys based on translated strings.

Bad:

```text
"Créer une organisation"
```

Good:

```text
organization.create
```

---

## 9. Translation File Structure

Recommended structure:

```text
/locales
├── ar
│   ├── common.json
│   ├── auth.json
│   ├── dashboard.json
│   ├── organizations.json
│   ├── users.json
│   ├── billing.json
│   ├── notifications.json
│   └── errors.json
│
├── fr
│   ├── common.json
│   ├── auth.json
│   ├── dashboard.json
│   ├── organizations.json
│   ├── users.json
│   ├── billing.json
│   ├── notifications.json
│   └── errors.json
│
└── en
    ├── common.json
    ├── auth.json
    ├── dashboard.json
    ├── organizations.json
    ├── users.json
    ├── billing.json
    ├── notifications.json
    └── errors.json
```

The exact framework/library may change, but the conceptual separation must remain.

---

## 10. Translation Key Conventions

Keys must describe meaning, not wording.

Recommended:

```text
auth.login
auth.logout
auth.email
auth.password
auth.invalidCredentials

dashboard.welcome
dashboard.overview
dashboard.recentActivity

organization.create
organization.members
organization.settings

billing.invoice
billing.paymentMethod
billing.paymentFailed
```

Do not create duplicate keys for identical meanings without a reason.

---

## 11. Shared Translation Vocabulary

Nexora must maintain consistent terminology.

For example:

```text
Organization
Workspace
Member
Administrator
Project
Task
Client
Invoice
Subscription
Role
Permission
Settings
Notification
Activity
```

The terminology must remain consistent across:

- UI
- API documentation
- Emails
- Notifications
- Help documentation
- Marketing pages

Translation decisions should be documented when terminology is ambiguous.

---

## 12. Arabic RTL Requirements

Arabic must activate RTL mode at the application root.

Conceptually:

```html
<html lang="ar" dir="rtl">
```

For French and English:

```html
<html lang="fr" dir="ltr">
<html lang="en" dir="ltr">
```

RTL must affect layout intentionally.

The following must be tested:

- Sidebar
- Navbar
- Dropdowns
- Forms
- Tables
- Cards
- Modals
- Pagination
- Breadcrumbs
- Charts
- Notifications
- Search
- Filters
- Authentication screens
- Billing screens
- Settings
- Empty states
- Error states

---

## 13. RTL Design Rules

Avoid hardcoded directional CSS whenever possible.

Prefer logical properties:

```css
margin-inline-start
margin-inline-end
padding-inline
inset-inline-start
inset-inline-end
text-align: start
```

Avoid relying heavily on:

```css
margin-left
margin-right
padding-left
padding-right
left
right
```

Icons that communicate direction must be reviewed individually.

Not every icon should automatically be mirrored.

Examples that may require mirroring:

- Back arrows
- Forward arrows
- Directional navigation

Examples that usually should not be mirrored:

- Logos
- Brand marks
- Universal icons
- Some object representations

---

## 14. Bidirectional Content

Some content may contain mixed Arabic, French, and English.

Examples:

- Names
- Email addresses
- URLs
- Company names
- Product names
- Invoice references
- IDs
- Technical strings

The interface must remain readable when mixed-direction content appears.

Examples:

```text
اسم الشركة: Nexora Labs
Email: hello@nexora.dev
Invoice: INV-2026-00421
```

Use appropriate Unicode/bidirectional handling rather than manual spacing hacks.

---

## 15. Dates and Times

Dates must never be formatted using hardcoded strings.

Use locale-aware formatting.

Examples:

```text
ar → ٢١ أغسطس ٢٠٢٦
fr → 21 août 2026
en → August 21, 2026
```

The exact formatting may vary by context.

The application must support:

- Date
- Date + time
- Relative time
- Month/year
- Weekdays
- Duration
- Calendar views

Time zones must be handled independently from locale.

---

## 16. Numbers

Numbers must use locale-aware formatting.

Examples:

```text
ar → locale-aware Arabic presentation
fr → 1 234,56
en → 1,234.56
```

Do not manually build formatted numbers using string concatenation.

---

## 17. Currency

Currency formatting must be locale-aware.

Nexora must distinguish between:

```text
currency code
currency symbol
numeric value
locale
```

Example:

```text
currency = DZD
locale = fr
```

may be rendered differently from:

```text
currency = DZD
locale = en
```

The currency itself must never be inferred solely from the language.

---

## 18. Pluralization

Pluralized messages must use the localization system's pluralization rules.

Do not write:

```text
1 project
2 projects
```

using manual conditionals scattered throughout components.

Use semantic translation keys and locale-aware plural rules.

Arabic pluralization must be treated separately because its plural rules are significantly richer than simple singular/plural models.

---

## 19. Validation Messages

Validation messages are user-facing content and must be localized.

Examples:

```text
auth.emailRequired
auth.invalidEmail
auth.passwordRequired
organization.nameRequired
billing.paymentFailed
```

Backend validation errors must expose stable machine-readable error codes.

Example:

```json
{
  "error": {
    "code": "AUTH_INVALID_CREDENTIALS",
    "message": "..."
  }
}
```

The frontend should resolve the final localized message using the error code whenever appropriate.

This prevents the API from becoming permanently tied to one natural language.

---

## 20. API Error Messages

API responses must separate:

```text
machine-readable error code
human-readable message
```

Example:

```json
{
  "error": {
    "code": "ORGANIZATION_NOT_FOUND",
    "message": "..."
  }
}
```

The `code` is stable.

The `message` may be localized.

Backend services must never depend on frontend-specific translated strings for business logic.

---

## 21. Emails

Transactional emails must support:

```text
ar
fr
en
```

Examples:

- Welcome email
- Invitation email
- Password reset
- Organization invitation
- Billing notification
- Payment confirmation
- Security alerts
- System notifications

The email locale should normally follow the user's configured locale.

Email templates must not contain hardcoded language-specific business logic.

---

## 22. Notifications

In-app and email notifications should use localized message templates.

Example:

```text
notification.projectAssigned
notification.taskCompleted
notification.invoicePaid
notification.memberInvited
```

Stored notification records should preserve enough structured information to allow proper rendering.

Avoid storing only a final translated sentence when the notification can be reconstructed from structured data.

---

## 23. User-Generated Content

Not all content in Nexora should be translated.

User-generated data remains user data.

Examples:

- Project names
- Client names
- Comments
- Task titles
- Notes
- Company names
- Uploaded documents

Nexora should display these values as entered unless a dedicated translation feature exists.

---

## 24. Database Localization Strategy

Database fields must not automatically be duplicated for every locale.

Avoid:

```text
name_fr
name_ar
name_en
```

unless the business domain explicitly requires separately authored multilingual content.

Most user-generated data should remain locale-neutral.

System-managed content should use the translation infrastructure.

For genuinely multilingual domain content, use a deliberate translation model.

Example:

```text
Product
 └── ProductTranslation
      ├── locale
      └── name
```

This should only be introduced where the domain requires it.

---

## 25. URL Strategy

Nexora should decide early whether application URLs are locale-prefixed.

For the authenticated product, a locale-neutral strategy is generally preferable:

```text
/dashboard
/settings
/projects
```

while the selected locale controls presentation.

Marketing/content-heavy public pages may use localized routes when SEO requires them:

```text
/fr
/en
/ar
```

The final route strategy must be consistent across the platform.

---

## 26. SEO and Metadata

Public pages must support localized:

- Title
- Description
- Open Graph metadata
- Twitter/X metadata
- Canonical URLs
- Language metadata
- Alternate locale references

Arabic pages must expose the correct language and direction metadata.

SEO localization applies primarily to public/marketing content, not private application screens.

---

## 27. Browser Language Detection

On first visit Nexora may inspect:

```text
Accept-Language
```

but browser detection must never override an explicit user selection.

Example:

```text
Browser = ar
User selects = fr
Next visit = fr
```

The explicit user decision wins.

---

## 28. Language Switcher

The language switcher must be available in appropriate global locations.

Example:

```text
العربية
Français
English
```

The switcher must:

1. Update the active locale.
2. Persist the preference.
3. Update text direction.
4. Update formatted dates/numbers/currencies where applicable.
5. Persist across sessions.
6. Avoid unnecessary full-page reloads when the framework supports seamless switching.

---

## 29. Translation Fallback

Nexora must define a deterministic fallback chain.

Recommended:

```text
Requested locale
        ↓
Fallback locale
        ↓
Development warning
```

Example:

```text
ar → fr → en
```

However, fallback behavior should be configured deliberately rather than assuming one language is universally the fallback.

Missing translations must be observable during development.

Silent missing translations are unacceptable.

---

## 30. Missing Translation Policy

During development:

```text
Missing translation
→ visible warning
→ logging
→ development diagnostics
```

Production must not expose raw translation keys to users.

Example of what must never reach production:

```text
dashboard.welcome
organization.create
auth.invalidCredentials
```

unless intentionally used as a diagnostic fallback.

---

## 31. Translation Quality

Translations should be reviewed for:

- Meaning
- Tone
- Grammar
- Cultural appropriateness
- Consistent terminology
- UI length
- Technical correctness
- Arabic RTL presentation

Machine translation may assist development but must not automatically become the final authority for important customer-facing copy.

---

## 32. Localization and Product Copy

Localization applies to:

- Product UI
- Marketing pages
- Documentation
- Help center
- Emails
- Notifications
- Error messages
- Empty states
- Onboarding
- Billing
- Authentication

Marketing copy may require different adaptation per locale rather than literal translation.

---

## 33. Localization and Design System

The design system must be localization-safe.

Components must tolerate:

- Longer French strings
- Different English terminology
- Longer or shorter Arabic translations
- RTL layout
- Different line wrapping
- Different button widths
- Different table column lengths
- Different navigation lengths

Components must not assume that text fits a fixed width.

---

## 34. Localization and Accessibility

Language and direction must be semantically exposed to assistive technologies.

The application must correctly communicate:

```html
lang="ar"
dir="rtl"
```

or:

```html
lang="fr"
dir="ltr"
```

and:

```html
lang="en"
dir="ltr"
```

Accessibility testing must include all supported locales.

---

## 35. Localization Testing

Every major release must test:

### Functional

- Language switching
- Locale persistence
- Translation loading
- Missing keys
- Fallback behavior
- Date formatting
- Number formatting
- Currency formatting
- Pluralization

### Visual

- Arabic RTL layout
- French text expansion
- English text behavior
- Navigation
- Forms
- Tables
- Modals
- Notifications
- Mobile layouts
- Empty states
- Error states

### Regression

A feature is not considered complete if it works only in one locale.

---

## 36. Development Rules

Developers must follow these rules:

### Rule 1

Never hardcode user-facing strings.

### Rule 2

Never assume LTR.

### Rule 3

Never manually concatenate localized sentences.

### Rule 4

Never use translated text as business logic.

### Rule 5

Never rely on English as an architectural assumption.

### Rule 6

Never add Arabic as an afterthought.

### Rule 7

Every new user-facing string must have translations defined or explicitly marked as pending.

---

## 37. Definition of Done

A feature is considered localization-ready only when:

```text
[ ] All user-facing strings use translation keys
[ ] Arabic translation exists
[ ] French translation exists
[ ] English translation exists
[ ] RTL layout verified
[ ] LTR layout verified
[ ] Long strings tested
[ ] Dates tested
[ ] Numbers tested
[ ] Currency tested where applicable
[ ] Validation messages localized
[ ] Notifications localized
[ ] Emails localized where applicable
[ ] Accessibility checked
```

---

## 38. Initial Implementation Strategy

Localization should be implemented in stages.

### Stage 1 — Architecture

Implement:

- Locale model
- Locale resolution
- Translation loader
- Translation key conventions
- User preference
- Organization default
- Direction switching

### Stage 2 — Core Product

Localize:

- Authentication
- Navigation
- Dashboard
- Organizations
- Users
- Roles
- Permissions
- Settings

### Stage 3 — Business Features

Localize:

- Projects
- Tasks
- Clients
- Billing
- Notifications
- Reports
- Other domain modules

### Stage 4 — Public Experience

Localize:

- Landing page
- Pricing
- Documentation
- FAQ
- Legal pages
- Public onboarding

### Stage 5 — Quality

Perform:

- Arabic RTL audit
- French linguistic review
- English review
- Accessibility testing
- Responsive testing
- Translation completeness audit

---

## 39. Initial Locale Availability

The first production-ready release must support:

```text
Arabic     ✅
French     ✅
English    ✅
```

However, translation completeness may be phased.

The architecture must never require a migration or rewrite to add another language later.

Future locales may include:

```text
es
de
tr
```

or other markets when Nexora expands.

Adding them must primarily involve translation resources and locale configuration rather than architectural changes.

---

## 40. Strategic Decision

Nexora will support:

```text
AR + FR + EN
```

from the beginning.

This is the official localization decision.

Arabic and French are the priority languages for the initial Algerian market.

English is included from the start because the implementation cost is low when designed correctly, while adding it after the product has grown would require unnecessary refactoring across the frontend, backend, emails, validation, documentation, and marketing surface.

Localization is therefore a **foundational architectural requirement**, not a post-MVP cosmetic feature.

---

## 41. Relationship With Other Documents

This document must be referenced by:

```text
Architecture
Frontend
Backend
Design System
Authentication
Users / Organizations
Notifications
Billing
API
Testing
Roadmap
Marketing / Website
```

Any document describing user-facing behavior must respect the rules defined here.

---

## 42. Final Principle

Nexora should never be built as:

```text
French application
        ↓
translate to Arabic later
        ↓
add English later
```

It must be built as:

```text
            Nexora
               │
       ┌───────┼───────┐
       │       │       │
      AR      FR      EN
      RTL     LTR     LTR
       │       │       │
       └───────┼───────┘
               │
       Same product architecture
```

The language changes the presentation.

It must not change the underlying product architecture.