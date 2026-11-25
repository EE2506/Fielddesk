# Fielddesk Client Portal Source Add-on

This bundle contains the client-facing sign-up and dashboard screens plus the integration files that connect them to the existing Fielddesk agent workspace.

## Add to the full workspace

The bundle is a source add-on, not a standalone application. Copy the portal screens, store, and CSS into the matching paths in the existing Vue 3 / TypeScript / Vite / Tailwind workspace. For `src/App.vue`, `src/router.ts`, `src/style.css`, and `public/manus-routes.json`, merge the portal changes into the existing files rather than blindly replacing any customized versions. The full current app and landing-page materials are in the complete project package.

## Preview

Open `/client/signup` or choose **Client preview** in the agent workspace. Use the fictional invite code **ANA-4821**, or choose **Skip sign-up · view sample dashboard**. The sample dashboard shows the Life premium due 2 December and the separate Motor renewal due 10 December.

## Prototype boundaries

There is no backend, account verification, persistent storage, or insurer integration. Client profile information remains in memory only. Message and call-request flows are simulations and explicitly do not send or save anything. Use fictional details only.
