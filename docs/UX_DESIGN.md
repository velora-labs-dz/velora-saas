# VELORA — UX & DESIGN SYSTEM

## 1. Product experience

Velora should feel like a modern operating system for a business, not a legacy ERP.

Principles:

- fast
- clear
- calm
- predictable
- dense where useful
- minimal unnecessary decoration
- consistent across modules

## 2. Landing page

Current work:

- pixel-accurate landing page based on the current Nexora landing reference
- landing work is separate from the internal application domain

Landing-page completion must not become permission to delay backend tenancy work.

## 3. Application shell

The authenticated application should eventually have:

- organization switcher
- global navigation
- command/search
- notifications
- user menu
- context-aware module navigation

## 4. Every major page

Must define:

- loading
- empty
- success
- validation error
- authorization denied
- server error
- retry where reasonable

## 5. Forms

Forms should:

- preserve input after recoverable errors
- validate on server
- provide understandable messages
- avoid exposing implementation details

## 6. Tables

Eventually support:

- search
- filters
- sorting
- pagination
- column density
- row actions
- responsive alternatives

Do not load huge operational datasets into the browser.

## 7. Statuses

Use stable machine states and localized presentation.

Example:

```text
active → Actif / Active / نشط
```

The database stores `active`.

## 8. Design consistency

Shared components should be used for:

- buttons
- inputs
- selects
- dialogs
- drawers
- tables
- cards
- badges
- empty states
- confirmations

Do not create a unique style for every module.

## 9. Mobile

Responsive first.

Critical quick operations should remain usable on mobile.

Management-heavy workflows may remain desktop-first.

## 10. Accessibility

Target:

- keyboard navigation
- visible focus
- semantic labels
- readable contrast
- accessible dialogs
- screen reader-friendly controls

Accessibility gets progressively stronger as the product matures.
