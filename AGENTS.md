# Easywords App

This file is for coding agents working in this repo. Follow it literally.

## Project context

- **Easywords App (`<easywordsapp.ru>`) is a SaaS app**: Allows you to learn words easily. Self-hosted application which you can use for quickly learn new words.
- **Operate like a cofounder.** Optimize for user value and speed, without compromising basic maintainability.

## Non‑negotiables

- **Do not overwrite user edits.** The user may change code between messages. If something changed, understand *why* and build on it.
- **Keep changes simple.** Implement the smallest change that solves the problem (unless you’re writing tests).
- **Fix root causes.** When debugging, gather enough info to understand the failure and fix it at the source (not via band-aids).

## Architecture & structure (Laravel)

- **Prefer small, verb-named Actions.** Avoid generic “Service/Manager/Handler” classes.
- **Controllers stay thin.** Single-action controllers are preferred.
- **Avoid events unless necessary.** Keep code flow obvious without jumping between files.
- **Jobs are thin + idempotent.** Delegate business logic to Actions.
- **If you create a model, also create a factory + seeder.**

## Contribution Guidelines
1. All new features must include tests
2. Use Laravel Actions for business logic
3. Follow strict typing conventions
4. Update documentation for API changes
5. Ensure all code quality checks pass before merging

## Code style (PHP)

- **Document intent** for non-obvious code (explain *why*, not *what*).
- **Purpose docblocks are required.** Every class/trait/interface/enum under `app/` must have a top-level PHPDoc block explaining:
    - why the file exists,
    - why the logic was extracted there (vs inlining),
    - what callers should rely on (the “contract”) when it’s non-obvious.
- **Import namespaces.** Don’t rely on implicit/global imports.
- **Avoid ambiguous names.** No one-letter variables unless extremely local and obvious.
- **Use guard clauses** over deep nesting.
- **No debugging helpers** in committed code (`dd()`, `dump()`, etc.).
- **Never use `@`** (PHP error suppression). If you truly must, document why and prefer explicit alternatives.
- **Default to `protected`** for non-public methods/properties unless there’s a strong reason.

## Laravel conventions & dependency boundaries

- **Do things the Laravel way.** Use helpers/Collections/Facades/attributes.
- **Do not use dependency injection.** Use Facades, Real-Time Facades, or `app()`.
- **Do not call `env()`** outside config files.
- **Prefer named routes** + `route()` over hardcoded URLs (including in app code).
- **Prefer helpers over Facades** when available (e.g. `session()` over `Session::get()`).
- **Avoid raw queries.** If unavoidable, parameterize and document why.

## Data & migrations

- **Migrations should be reversible** when possible.
- **Never edit old migrations** after they’ve been merged. Create a new migration.

## Frontend (React.js, typescript, shadcn)

- **HTML must be tidy, valid, semantic, and accessible.**
- **Close inline tags** (`<meta />`, `<img />`, `<br />`, …).
- **Prefer landmarks** (`header`, `nav`, `main`, `footer`) over generic wrappers.
- **Keep focus outlines.** Focus states should be visible and intentional.
- **Every input needs a `<label>`** (via `for` + `id`) unless there’s a strong reason.
- **Icons:** decorative icons get `aria-hidden="true"`; informative icons need an accessible name.

### Styling

- Prefer shadcn utilities over custom CSS.
- If custom CSS is necessary, keep it minimal and document why.
- Extract repeated UI patterns into Blade components (don’t copy/paste huge class strings).

### Interactivity

- Alpine code belongs in the Blade component.
- Use Feature-Sliced pattern to structure project
- Keep state small and local (avoid hidden global state).

## Testing (PHPUnit)

- Test files mirror `./app` structure 1:1 when possible.
    - If there is no matching `app/` file, only then place tests at the root (e.g. `./tests/Feature`) with a clear justification.
- Avoid hardcoded hosts/URLs; prefer `route()` / `url()`.
- Prefer strict fakes over permissive mocks.
- Tests must be parallel-safe: avoid shared fixed file paths and clean up created files.
- Use Real-Time Facades if you need to mock something resolved from the container.

## Tooling / definition of done

- **Format:** `task fix`
- **Tests:** `task test`
- **Sanity:** no debug helpers left behind; migrations reversible; UI remains accessible; minimal change set.

## Default review behavior (whenever you touch code)

- **Existence check**: for every `app/` file you create or edit, confirm it earns its existence. If it’s redundant/unused/over-abstracted, prefer deleting/merging/moving it (and updating routes/usages/tests).
- **Logic check**: inside kept files, remove or simplify any code that isn’t justified (dead branches, unused options, placeholder copy, unnecessary indirection).
- **Test alignment**: keep tests mirrored to `app/` structure 1:1 when possible; update or delete tests alongside code changes.
