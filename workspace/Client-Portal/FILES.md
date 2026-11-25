# Fielddesk Client Portal — Download File List

The [client portal source bundle](/home/ubuntu/fielddesk-client-portal.zip) contains the following files:

## Portal screens and state

- `src/views/ClientSignupView.vue` — sign-up form, demo agent invite code, required-field validation, and sample-account shortcut.
- `src/views/ClientDashboardView.vue` — connected-agent view, fictional Life and Motor policy summaries, and simulated contact dialogs.
- `src/stores/clientPortal.ts` — in-memory demo profile, invite code `ANA-4821`, and fictional sample agent/policies.
- `src/client-portal.css` — responsive styles for the separate client-facing screens.

## Integration files

- `src/App.vue` — separate client portal shell, client route handling, and links between portal and agent workspace.
- `src/router.ts` — `/client/signup` and `/client/dashboard` routes plus `/client` redirect.
- `src/style.css` — agent-workspace styling for the **Client preview** entry point.
- `public/manus-routes.json` — route manifest including the two client pages.

## Included guidance

- `README.md` — how to use and integrate the bundle, demo boundaries, and sample code.
- `CLIENT-PORTAL-HANDOFF.md` — client-flow fields, validation, and resulting states.
- `FILES.md` — this inventory, included inside the ZIP as well.

This is a source add-on for the existing Vue 3, TypeScript, Vite, and Tailwind Fielddesk workspace; it is not a standalone deployable app. The full app and landing-page materials are in the [complete project package](/home/ubuntu/fielddesk-complete-project.zip). All sample data is fictional; profile data stays in memory and contact actions do not send messages or calls.
