# Repository Guidelines

## Project Structure & Module Organization
- `app/` holds domain services, HTTP controllers, jobs, and providers; keep controllers thin by delegating to service classes.
- `resources/` contains Blade views (`resources/views`) and Vite-managed assets (`resources/js`, `resources/css`); `resources/js/app.js` is the main entrypoint.
- `routes/` defines HTTP entry points (`web.php`, `api.php`, `console.php`); group related routes under feature-specific prefixes.
- `database/` stores migrations, seeders, and factories consumed by tests.
- `tests/` uses Pest with PSR-4 autoloading; mirror production namespaces so files are discoverable.

## Build, Test, and Development Commands
- `composer run setup` provisions a fresh environment (dependencies, `.env`, key, migrations, initial asset build).
- `composer run dev` launches the PHP server, queue listener, log tailing, and Vite watcher via `concurrently`.
- `npm run dev` starts the Vite asset pipeline alone for rapid front-end iteration.
- `npm run build` produces versioned, minified assets for deployment.
- `composer run test` clears cached config and executes the full Laravel test suite.

## Coding Style & Naming Conventions
- Follow PSR-12: 4-space indentation, StudlyCaps classes, camelCase methods/properties, and snake_case config keys.
- Run `./vendor/bin/pint` before committing to apply Laravel Pint formatting and linting rules.
- Blade partials belong in `resources/views` (use hyphenated file names, e.g., `layouts/app.blade.php`); reusable components go under `resources/views/components`.
- JavaScript modules are ES modules; reserve `PascalCase` for UI components and `kebab-case.js` for utilities.

## Testing Guidelines
- Write Pest tests under `tests/Feature` and `tests/Unit`; name files `<Subject>Test.php`.
- Use factories (`database/factories`) with the `RefreshDatabase` trait to keep tests isolated.
- Run `./vendor/bin/pest --coverage` to audit coverage; keep critical domains at or above 90%.

## Commit & Pull Request Guidelines
- Follow Conventional Commits (`feat:`, `fix:`, `chore:`) with 72-character subjects; add context and testing notes in the body.
- Link issues or task IDs where available and describe behavioural impact plus migration steps.
- Pull requests should outline purpose, list test evidence, attach UI screenshots, and note post-merge actions.
- Ensure `composer run test` and `npm run build` pass locally before requesting review or merging.

## Environment & Security Notes
- Copy `.env.example` to `.env`, set a unique `APP_KEY`, and never commit secrets; rotate keys after leaks.
- Manage queues and logs through `composer run dev`; stop spawned workers explicitly (`Ctrl+C`) to avoid orphaned listeners.
