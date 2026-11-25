# Fielddesk

Fielddesk is an insurance-agent workspace and client portal. Agents track follow-ups, policy deadlines, and document requirements. Clients can request an introduction to an available agent and see its status.

## What works today

| Area | Features |
| --- | --- |
| Agent workspace | Today dashboard, client records, policies, document checklists, contact history, callbacks, and reminder preferences |
| Client portal | Client registration, agent discovery, introduction requests, and accepted connections |
| Access | Separate Client and Agent routes. Workspace records are scoped to their owning agent, and clients see their own introduction requests. |

The application uses PHP 8.4, Laravel 13, Inertia, Vue 3, TypeScript, and Tailwind CSS. SQLite is the default for local development. MySQL 8 is supported through the project's local database setup command.

## Run locally

Install PHP 8.4 with PDO SQLite and the standard Laravel extensions, Composer, and a Node.js version supported by Vite. From a fresh clone in PowerShell:

```powershell
composer install
npm ci
Copy-Item .env.example .env
php artisan key:generate
New-Item -ItemType File database/database.sqlite
php artisan migrate
npm run build
php artisan serve --host=127.0.0.1
```

Open the address printed by Artisan. With its default port, the main pages are:

| Page | URL |
| --- | --- |
| Landing page | http://127.0.0.1:8000/ |
| Client sign-up | http://127.0.0.1:8000/client/signup |
| Agent and Client sign-in | http://127.0.0.1:8000/login |
| Client dashboard | http://127.0.0.1:8000/portal |
| Agent workspace | http://127.0.0.1:8000/workspace |

Use the sign-up page to create a Client account. To create a local Agent account, run the following command and use the credentials it prints on first creation:

```powershell
php artisan db:seed --class=DevelopmentAgentSeeder
```

In the Agent workspace, enable introductions in **Reminders** to make that agent visible to Clients. For MySQL 8, prepare an empty local schema and database user, stop the app, then run `php artisan fielddesk:mysql` to switch the local database.

## Verify changes

```powershell
npm run typecheck
php vendor/bin/phpunit
```

Use synthetic data for local review. Client policy linking, portal messaging, callback requests from Clients, and automated reminders are planned. The landing-page signup is a demo and does not collect submissions.
