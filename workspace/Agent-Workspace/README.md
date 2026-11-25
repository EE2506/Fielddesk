# Fielddesk — Insurance Agent Workspace prototype

A clickable, mobile-first prototype for quickly finding client callbacks, call reasons, and prior agreements. It starts on Today with fictional work for Wednesday, 25 November 2026 in Manila.

## Run locally

```bash
pnpm install
pnpm dev
```

Production build and strict Vue/TypeScript check:

```bash
pnpm run build
```

## Where to look

- `src/views/TodayView.vue` — callbacks, policy deadlines, demo date controls.
- `src/views/ClientsView.vue` and `ClientDetailView.vue` — lookup and contact history.
- `src/views/PolicyDetailView.vue` — policy dates, filing reference, document statuses.
- `src/views/ContactLogView.vue` — contact outcomes, callback scheduling, save retry.
- `src/views/ClientFormView.vue`, `PolicyFormView.vue`, `SettingsView.vue` — add/edit and reminder forms.
- `src/seed.ts`, `src/models.ts`, `src/stores/workspace.ts` — local mock data and persistence.
- `HANDOFF.md` — scenario steps and action → fields → validation → state map.

All data and interactions are frontend-only. Calls/texts are simulated, numbers are masked, and local changes persist in this browser until **Reset demo** is selected. Laravel integration is intentionally deferred until design review.
