# Easywords App

This file is for coding agents working in this repo. Follow it literally.

## Project context

- **Easywords App (`<easywordsapp.ru>`) is a SaaS vocabulary learning platform**: Users save words through mobile or web interface, study them using flashcards, and get AI-generated usage examples.
- **Tech stack**: Laravel 12 + Inertia.js + React + TypeScript. API follows JSON:API specification. Admin panel built with Filament 4.
- **Multi-language support**: Russian, English, German. All UI text must be translatable.
- **Operate like a cofounder.** Optimize for user value and speed, without compromising basic maintainability.

## Core features

- **Word management**: Users save words with translations and definitions
- **Flashcard learning**: Flip-card interface for active recall practice
- **AI-powered examples**: Context-aware usage examples for each word
- **Multi-platform**: Web interface and mobile app with shared backend
- **Admin panel**: Filament 4-based administration interface

## Non‑negotiables

- **Do not overwrite user edits.** The user may change code between messages. If something changed, understand *why* and build on it.
- **Keep changes simple.** Implement the smallest change that solves the problem (unless you're writing tests).
- **Fix root causes.** When debugging, gather enough info to understand the failure and fix it at the source (not via band-aids).
- **Strict typing everywhere.** Maximum PHPStan level on backend, strict TypeScript on frontend.
- **Everything must be translatable.** No hardcoded UI text. Support ru/en/de languages.

## Architecture & structure (Laravel + Inertia)

- **Prefer small, verb-named Actions.** Avoid generic "Service/Manager/Handler" classes.
- **Controllers stay thin.** Single-action controllers are preferred. Return Inertia responses for pages, JSON:API-compliant responses for API endpoints.
- **Avoid events unless necessary.** Keep code flow obvious without jumping between files.
- **Jobs are thin + idempotent.** Delegate business logic to Actions.
- **If you create a model, also create a factory + seeder.**
- **API must follow JSON:API specification** (`application/vnd.api+json`, proper resource objects, relationships, includes).

## Routing (spatie/laravel-route-attributes)

- **Do not define routes in route files.** Use PHP 8 attributes on controller methods via `spatie/laravel-route-attributes`.
- **Attribute placement**: place route attributes directly above controller methods.
- **Middleware attributes**: use `#[Middleware]` on class level for all methods, or on method level for specific routes.
- **Named routes**: always specify route names via `name:` parameter.

### Route Attribute Examples

```php
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Middleware;

#[Middleware('auth')]
class WordController
{
    #[Get('words', name: 'words.index')]
    public function index(): Response
    {
        // ...
    }

    #[Post('words', name: 'words.store')]
    #[Middleware('throttle:60,1')]
    public function store(StoreWordRequest $request): Response
    {
        // ...
    }

    #[Get('api/words/{word}', name: 'api.words.show')]
    public function show(Word $word): JsonResponse
    {
        // ...
    }
}
```

## API Responses (Laravel Data)

- **Always return Laravel Data objects, never raw models** in API responses.
- **Data classes**: place in `app/Data/{Entity}Data.php`.
- **Type everything**: use strict types in Data constructors.
- **Use transformers**: leverage `toArray()`, `toJson()`, `toResponse()` methods.
- **Collections**: use regular array with generic types.
- **Validation**: use Data validation attributes when appropriate.

### Laravel Data Examples

```php
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Max;

class WordData extends Data
{
    public function __construct(
        public int $id,
        #[Required, Max(255)]
        public string $word,
        public string $translation,
        public ?string $definition,
        public string $language,
        public Carbon $created_at,
    ) {}
}

// In controller
#[Get('api/words/{word}', name: 'api.words.show')]
public function show(Word $word): JsonResponse
{
    return WordData::from($word)->toResponse();
}

// For collections
#[Get('api/words', name: 'api.words.index')]
public function index(): \Spatie\LaravelData\PaginatedDataCollection
{
    return WordData::collection(Word::paginate(), \Spatie\LaravelData\PaginatedDataCollection::class);
}
```

### Data Structure Guidelines

- **One Data class per entity**: `WordData`, `ExampleData`, `UserData`, etc.
- **Nested Data objects**: use Data objects for relationships (e.g., `public UserData $user`).
- **Factory methods**: add static `fromModel()` or `fromArray()` when transformation logic is complex.

## Contribution Guidelines
1. All new features must include tests (strict coverage requirements)
2. Use Laravel Actions for business logic
3. Follow strict typing conventions (PHPStan max level, strict TypeScript)
4. Update documentation for API changes
5. Ensure all code quality checks pass before merging
6. API changes must maintain JSON:API compliance
7. Always use route attributes, never route files
8. Always return Data objects from API endpoints
9. All UI text must be translatable (no hardcoded strings)

## Code style (PHP)

- **Document intent** for non-obvious code (explain *why*, not *what*).
- **Purpose docblocks are required.** Every class/trait/interface/enum under `app/` must have a top-level PHPDoc block explaining:
    - why the file exists,
    - why the logic was extracted there (vs inlining),
    - what callers should rely on (the "contract") when it's non-obvious.
- **Import namespaces.** Don't rely on implicit/global imports.
- **Avoid ambiguous names.** No one-letter variables unless extremely local and obvious.
- **Use guard clauses** over deep nesting.
- **No debugging helpers** in committed code (`dd()`, `dump()`, etc.).
- **Never use `@`** (PHP error suppression). If you truly must, document why and prefer explicit alternatives.
- **Default to `protected`** for non-public methods/properties unless there's a strong reason.
- **Strict types declaration** (`declare(strict_types=1);`) in every PHP file.
- **Type everything**: parameters, return types, properties. No mixed types without strong justification.

## Laravel conventions & dependency boundaries

- **Do things the Laravel way.** Use helpers/Collections/Facades/attributes.
- **Do not use dependency injection.** Use Facades, Real-Time Facades, or `app()`.
- **Do not call `env()`** outside config files.
- **Prefer named routes** + `route()` over hardcoded URLs (including in app code).
- **Prefer helpers over Facades** when available (e.g. `session()` over `Session::get()`).
- **Avoid raw queries.** If unavoidable, parameterize and document why.
- **Inertia responses** for page controllers, **Data objects** for API controllers.

## Admin Panel (Filament 4)

- **Keep Resources clean**: extract form schemas and table schemas into separate classes. See UserResource for example
- **Extract complex fields**: if a field definition exceeds ~15 lines or contains complex logic, extract it into its own class in `app/Filament/Resources/{ResourceName}/Fields/{FieldName}Field.php`
- **Keep field classes focused**: one field = one class with a static `make()` method returning the configured field.
- **Document extraction reasoning**: when extracting fields, explain why (complexity, reusability, clarity).

### Filament Conventions

- **Schema classes must have static methods** (`schema()`, `columns()`, `filters()`, etc.) returning arrays.
- **Field classes must have a static `make()` method** returning the configured field instance.
- **Type everything**: use PHPStan-compatible return types (`array<int, Field>`, etc.).
- **Keep Resources as coordinators**: Resources should mostly delegate to schema/field classes and define pages/navigation.
- **Avoid inline closures in schemas**: extract complex logic to Actions or dedicated methods.
- **Document schema purpose**: explain why schema was extracted (complexity, reusability).

## API Design (JSON:API)

- **Follow JSON:API spec strictly**: `application/vnd.api+json` content type, proper resource structure.
- **Resource objects** must include `type` and `id` at minimum.
- **Use relationships** for related data, support `include` parameter for eager loading.
- **Pagination** via `page[number]` and `page[size]` query parameters.
- **Filtering** via `filter[attribute]` query parameters.
- **Sparse fieldsets** via `fields[type]` when beneficial.
- **Error responses** must follow JSON:API error object format.
- **Always use Laravel Data objects** for responses, never return raw Eloquent models.

## Data & migrations

- **Migrations should be reversible** when possible.
- **Never edit old migrations** after they've been merged. Create a new migration.

## Frontend (React + TypeScript + Inertia + shadcn/ui)

- **Structure follows Feature-Sliced Design (FSD)**: organize by features, not by file types.
- **Strict TypeScript**: no `any`, no `@ts-ignore` without documentation, enable all strict flags.
- **Inertia-first communication**: use Inertia's built-in mechanisms for all server communication.
- **HTML must be tidy, valid, semantic, and accessible.**
- **Close inline tags** (`<meta />`, `<img />`, `<br />`, etc).
- **Prefer landmarks** (`header`, `nav`, `main`, `footer`) over generic wrappers.
- **Keep focus outlines.** Focus states should be visible and intentional.
- **Every input needs a `<label>`** (via `htmlFor` + `id`) unless there's a strong reason.
- **Icons:** decorative icons get `aria-hidden="true"`; informative icons need an accessible name.

### Inertia Communication Patterns

- **Sending data to server**: always use `useForm` hook from `@inertiajs/react` [web:23][web:26].
- **Receiving data from server**: always via props typed in component signature.
- **Never use axios/fetch** for standard operations - Inertia handles it.
- **Form submission**: use `form.post()`, `form.put()`, `form.patch()`, `form.delete()`.
- **File uploads**: `useForm` handles `multipart/form-data` automatically when form includes files.

### Inertia Form Examples

```tsx
import { Form } from '@inertiajs/react'
import { store } from 'App/Http/Controllers/UserController'

export default () => (
    <Form action={store()}>
        <input type="text" name="name" />
        <input type="email" name="email" />
        <button type="submit">Create User</button>
    </Form>
)
```

### TypeScript Typing

- **Type all component props**: create interface for every component's props.
- **Type all form data**: create interface for `useForm` data structure.
- **Type Inertia page props**: extend `PageProps` type from `@inertiajs/react`.
- **No implicit any**: all variables, parameters, returns must be explicitly typed.
- **Use strict mode**: enable all strict TypeScript compiler options.

```tsx
import { PageProps } from '@inertiajs/react';

interface WordData {
  id: number;
  word: string;
  translation: string;
  language: string;
  created_at: string;
}

interface WordIndexProps extends PageProps {
  words: {
    data: WordData[];
    meta: {
      current_page: number;
      total: number;
    };
  };
}

export default function WordIndex({ words }: WordIndexProps) {
  // Component implementation
}
```

### Internationalization (i18n)

- **All UI text must be translatable** - no hardcoded strings in components [web:27][web:30].
- **Supported languages**: Russian (ru), English (en), German (de).
- **Laravel translations**: store in `resources/js/shared/i18n/{locale}.ts` files.
- **Pass translations via Inertia**: share translations in HandleInertiaRequests middleware.
- **Use translation function**: create `useTranslation()` hook or access via props.

#### i18n Implementation Example

```php
// app/Http/Middleware/HandleInertiaRequests.php
public function share(Request $request): array
{
    return [
        ...parent::share($request),
        'locale' => app()->getLocale(),
        'translations' => [
            'common' => __('common'),
            'words' => __('words'),
        ],
    ];
}
```

```tsx
// Component usage
import { usePage } from '@inertiajs/react';

interface SharedProps {
  locale: string;
  translations: {
    common: Record<string, string>;
    words: Record<string, string>;
  };
}

export default function WordCard() {
  const { translations } = usePage<SharedProps>().props;
  
  return (
    <button>{translations.words.save}</button>
  );
}
```

```json
// resources/js/shared/i18n/en.ts
{
  "words": {
    "save": "Save",
    "cancel": "Cancel",
    "add_word": "Add Word"
  }
}

// resources/js/shared/i18n/ru.ts
{
  "words": {
    "save": "Сохранить",
    "cancel": "Отмена",
    "add_word": "Добавить слово"
  }
}

// resources/js/shared/i18n/de.ts
{
  "words": {
    "save": "Speichern",
    "cancel": "Abbrechen",
    "add_word": "Wort hinzufügen"
  }
}
```

### FSD Structure

```
src/
├── app/           # App initialization, providers, routing
├── pages/         # Inertia page components
├── widgets/       # Complex composite features (e.g., WordCard, FlashcardViewer)
├── features/      # User scenarios (e.g., SaveWord, StudyFlashcards, ViewExamples)
├── entities/      # Business entities (e.g., Word, User, Example)
├── shared/        # Reusable code (ui, lib, api, types)
│   ├── ui/        # shadcn/ui components
│   ├── lib/       # Utilities, hooks (useTranslation, etc.)
│   ├── types/     # TypeScript types/interfaces
│   └── config/    # Constants, configurations
```

### Brand Colors & Styling

Based on the Easywords logo, use this color palette:

```javascript
// tailwind.config.js
module.exports = {
  theme: {
    extend: {
      colors: {
        // Primary - Blue (from logo box and icon)
        primary: {
          DEFAULT: '#1E5F8C',  // Dark blue
          50: '#E8F1F7',
          100: '#D1E4EF',
          200: '#A3C9DF',
          300: '#75AECF',
          400: '#4793BF',
          500: '#1E5F8C',  // Main
          600: '#184C70',
          700: '#123954',
          800: '#0C2638',
          900: '#06131C',
        },
        // Secondary - Green (from logo text)
        secondary: {
          DEFAULT: '#7CB342',  // Lime green
          50: '#F3F8EC',
          100: '#E7F1D9',
          200: '#CFE3B3',
          300: '#B7D58D',
          400: '#9FC467',
          500: '#7CB342',  // Main
          600: '#638F35',
          700: '#4A6B28',
          800: '#31471A',
          900: '#19240D',
        },
        // Accent - Dark Green (from "EASY" text)
        accent: {
          DEFAULT: '#33691E',
          50: '#EBF2E6',
          100: '#D7E5CD',
          200: '#AFCB9B',
          300: '#87B169',
          400: '#5F9737',
          500: '#33691E',  // Main
          600: '#295418',
          700: '#1F3F12',
          800: '#142A0C',
          900: '#0A1506',
        },
      },
    },
  },
};
```

### Styling Guidelines

- Use shadcn/ui components as foundation.
- **Use only built-in Tailwind utilities** - no custom CSS, no inline styles, no custom classNames.
- **Brand colors**: use `primary` (blue) for main actions, `secondary` (green) for highlights, `accent` (dark green) for emphasis.
- **If you need a color not in the palette** - add it to `tailwind.config.js` first, then use via utility classes.
- **Never write custom CSS** unless there's an exceptional case that cannot be solved with Tailwind utilities (document why in such rare cases).
- Extract repeated UI patterns into reusable components (don't copy/paste class strings).
- **Color usage examples**:
  - Primary buttons: `bg-primary hover:bg-primary-600`
  - Success states: `bg-secondary text-white`
  - Links/emphasis: `text-accent hover:text-accent-600`

### State Management

- Use Inertia's built-in state management for page data.
- Keep component state small and local.
- For shared state, prefer React Context or Zustand (document why if introduced).
- **Never bypass Inertia** - don't use external state management for server data.

## Testing (PHPUnit)

- **Strict coverage requirements**: all Actions, Models, API endpoints must have tests.
- Test files mirror `./app` structure 1:1 when possible.
    - If there is no matching `app/` file, only then place tests at the root (e.g. `./tests/Feature`) with a clear justification.
- **API tests must verify JSON:API compliance**: structure, content types, relationships.
- **Test Data objects**: verify transformations, validation, and JSON structure.
- **Filament tests**: test custom field classes, form submissions, table filters/actions.
- **Test translations**: verify all supported languages have required keys.
- Avoid hardcoded hosts/URLs; prefer `route()` / `url()`.
- Prefer strict fakes over permissive mocks.
- Tests must be parallel-safe: avoid shared fixed file paths and clean up created files.
- Use Real-Time Facades if you need to mock something resolved from the container.
- **Test AI integrations** with proper mocks/fakes for external services.

## Tooling / definition of done

- use `task all` command, it will make:
- **Format**
- **Static analysis:** Code must pass maximum PHPStan level
- **Type check:** for TypeScript
- **Tests:** (all tests passing)
- **Sanity:** no debug helpers left behind; migrations reversible; UI remains accessible; minimal change set; JSON:API compliance verified; Data objects used for API responses; routes defined via attributes; all UI text translatable.

## Default review behavior (whenever you touch code)

- **Existence check**: for every `app/` file you create or edit, confirm it earns its existence. If it's redundant/unused/over-abstracted, prefer deleting/merging/moving it (and updating routes/usages/tests).
- **Logic check**: inside kept files, remove or simplify any code that isn't justified (dead branches, unused options, placeholder copy, unnecessary indirection).
- **Type safety**: verify strict types are used everywhere, no escape hatches without justification.
- **Test alignment**: keep tests mirrored to `app/` structure 1:1 when possible; update or delete tests alongside code changes.
- **API compliance**: verify JSON:API spec adherence for all API endpoints.
- **Data object usage**: verify API controllers return Data objects, not raw models.
- **Route attributes**: verify routes are defined via attributes on controller methods, not in route files.
- **Inertia patterns**: verify forms use `useForm`, data comes via props, no axios/fetch for standard operations.
- **TypeScript strictness**: verify all types are explicit, no `any`, no `@ts-ignore` without justification.
- **i18n compliance**: verify no hardcoded UI text, all strings translatable, all three languages supported.
- **FSD compliance**: verify frontend code follows Feature-Sliced Design principles.
- **Filament organization**: verify schemas and complex fields are properly extracted from Resources.
- **Brand consistency**: verify colors follow defined palette (primary/secondary/accent).

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.5
- filament/filament (FILAMENT) - v4
- inertiajs/inertia-laravel (INERTIA_LARAVEL) - v2
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v4
- laravel/wayfinder (WAYFINDER) - v0
- livewire/livewire (LIVEWIRE) - v3
- larastan/larastan (LARASTAN) - v3
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- phpunit/phpunit (PHPUNIT) - v12
- rector/rector (RECTOR) - v2
- @inertiajs/react (INERTIA_REACT) - v2
- react (REACT) - v19
- tailwindcss (TAILWINDCSS) - v4
- @laravel/vite-plugin-wayfinder (WAYFINDER_VITE) - v0
- eslint (ESLINT) - v9
- prettier (PRETTIER) - v3

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-react-development` when working with Inertia client-side patterns.

# Inertia v2

- Use all Inertia features from v1 and v2. Check the documentation before making changes to ensure the correct approach.
- New features: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

## Laravel 12 Structure

- In Laravel 12, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app/Console/Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== wayfinder/core rules ===

# Laravel Wayfinder

Use Wayfinder to generate TypeScript functions for Laravel routes. Import from `@/actions/` (controllers) or `@/routes/` (named routes).

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should cover all happy paths, failure paths, and edge cases.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files; these are core to the application.

## Running Tests

- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).

=== inertia-react/core rules ===

# Inertia + React

- IMPORTANT: Activate `inertia-react-development` when working with Inertia React client-side patterns.

=== filament/filament rules ===

## Filament

- Filament is a Laravel UI framework built on Livewire, Alpine.js, and Tailwind CSS. UIs are defined in PHP via fluent, chainable components. Follow existing conventions in this app.
- Use the `search-docs` tool for official documentation on Artisan commands, code examples, testing, relationships, and idiomatic practices. If `search-docs` is unavailable, refer to https://filamentphp.com/docs.

### Artisan

- Always use Filament-specific Artisan commands to create files. Find available commands with the `list-artisan-commands` tool, or run `php artisan --help`.
- Inspect required options before running, and always pass `--no-interaction`.

### Patterns

Always use static `make()` methods to initialize components. Most configuration methods accept a `Closure` for dynamic values.

Use `Get $get` to read other form field values for conditional logic:

<code-snippet name="Conditional form field visibility" lang="php">
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;

Select::make('type')
    ->options(CompanyType::class)
    ->required()
    ->live(),

TextInput::make('company_name')
    ->required()
    ->visible(fn (Get $get): bool => $get('type') === 'business'),

</code-snippet>

Use `Set $set` inside `->afterStateUpdated()` on a `->live()` field to mutate another field reactively. Prefer `->live(onBlur: true)` on text inputs to avoid per-keystroke updates:

<code-snippet name="Reactive field update" lang="php">
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

TextInput::make('title')
    ->required()
    ->live(onBlur: true)
    ->afterStateUpdated(fn (Set $set, ?string $state) => $set(
        'slug',
        Str::slug($state ?? ''),
    )),

TextInput::make('slug')
    ->required(),

</code-snippet>

Compose layout by nesting `Section` and `Grid`. Children need explicit `->columnSpan()` or `->columnSpanFull()`:

<code-snippet name="Section and Grid layout" lang="php">
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

Section::make('Details')
    ->schema([
        Grid::make(2)->schema([
            TextInput::make('first_name')
                ->columnSpan(1),
            TextInput::make('last_name')
                ->columnSpan(1),
            TextInput::make('bio')
                ->columnSpanFull(),
        ]),
    ]),

</code-snippet>

Use `Repeater` for inline `HasMany` management. `->relationship()` with no args binds to the relationship matching the field name:

<code-snippet name="Repeater for HasMany" lang="php">
use Filament\Forms\Components\Repeater;

Repeater::make('qualifications')
    ->relationship()
    ->schema([
        TextInput::make('institution')
            ->required(),
        TextInput::make('qualification')
            ->required(),
    ])
    ->columns(2),

</code-snippet>

Use `state()` with a `Closure` to compute derived column values:

<code-snippet name="Computed table column value" lang="php">
use Filament\Tables\Columns\TextColumn;

TextColumn::make('full_name')
    ->state(fn (User $record): string => "{$record->first_name} {$record->last_name}"),

</code-snippet>

Use `SelectFilter` for enum or relationship filters, and `Filter` with a `->query()` closure for custom logic:

<code-snippet name="Table filters" lang="php">
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

SelectFilter::make('status')
    ->options(UserStatus::class),

SelectFilter::make('author')
    ->relationship('author', 'name'),

Filter::make('verified')
    ->query(fn (Builder $query) => $query->whereNotNull('email_verified_at')),

</code-snippet>

Actions are buttons that encapsulate optional modal forms and behavior:

<code-snippet name="Action with modal form" lang="php">
use Filament\Actions\Action;

Action::make('updateEmail')
    ->schema([
        TextInput::make('email')
            ->email()
            ->required(),
    ])
    ->action(fn (array $data, User $record) => $record->update($data)),

</code-snippet>

### Testing

Testing setup (requires `pestphp/pest-plugin-livewire` in `composer.json`):

- Always call `$this->actingAs(User::factory()->create())` before testing panel functionality.
- For edit pages, pass `['record' => $user->id]`, use `->call('save')` (not `->call('create')`), and do not assert `->assertRedirect()` (edit pages do not redirect after save).

<code-snippet name="Table test" lang="php">
use function Pest\Livewire\livewire;

livewire(ListUsers::class)
    ->assertCanSeeTableRecords($users)
    ->searchTable($users->first()->name)
    ->assertCanSeeTableRecords($users->take(1))
    ->assertCanNotSeeTableRecords($users->skip(1));

</code-snippet>

<code-snippet name="Create resource test" lang="php">
use function Pest\Laravel\assertDatabaseHas;

livewire(CreateUser::class)
    ->fillForm([
        'name' => 'Test',
        'email' => 'test@example.com',
    ])
    ->call('create')
    ->assertNotified()
    ->assertHasNoFormErrors()
    ->assertRedirect();

assertDatabaseHas(User::class, [
    'name' => 'Test',
    'email' => 'test@example.com',
]);

</code-snippet>

<code-snippet name="Edit resource test" lang="php">
livewire(EditUser::class, ['record' => $user->id])
    ->fillForm(['name' => 'Updated'])
    ->call('save')
    ->assertNotified()
    ->assertHasNoFormErrors();

assertDatabaseHas(User::class, [
    'id' => $user->id,
    'name' => 'Updated',
]);

</code-snippet>

<code-snippet name="Testing validation" lang="php">
livewire(CreateUser::class)
    ->fillForm([
        'name' => null,
        'email' => 'invalid-email',
    ])
    ->call('create')
    ->assertHasFormErrors([
        'name' => 'required',
        'email' => 'email',
    ])
    ->assertNotNotified();

</code-snippet>

Use `->callAction(DeleteAction::class)` for page actions, or `->callAction(TestAction::make('name')->table($record))` for table actions:

<code-snippet name="Calling actions" lang="php">
use Filament\Actions\Testing\TestAction;

livewire(ListUsers::class)
    ->callAction(TestAction::make('promote')->table($user), [
        'role' => 'admin',
    ])
    ->assertNotified();

</code-snippet>

### Correct Namespaces

- Form fields (`TextInput`, `Select`, `Repeater`, etc.): `Filament\Forms\Components\`
- Infolist entries (`TextEntry`, `IconEntry`, etc.): `Filament\Infolists\Components\`
- Layout components (`Grid`, `Section`, `Fieldset`, `Tabs`, `Wizard`, etc.): `Filament\Schemas\Components\`
- Schema utilities (`Get`, `Set`, etc.): `Filament\Schemas\Components\Utilities\`
- Table columns (`TextColumn`, `IconColumn`, etc.): `Filament\Tables\Columns\`
- Table filters (`SelectFilter`, `Filter`, etc.): `Filament\Tables\Filters\`
- Actions (`DeleteAction`, `CreateAction`, etc.): `Filament\Actions\`. Never use `Filament\Tables\Actions\`, `Filament\Forms\Actions\`, or any other sub-namespace for actions.
- Icons: `Filament\Support\Icons\Heroicon` enum (e.g., `Heroicon::PencilSquare`)

### Common Mistakes

- **Never assume public file visibility.** File visibility is `private` by default. Always use `->visibility('public')` when public access is needed.
- **Never assume full-width layout.** `Grid`, `Section`, `Fieldset`, and `Repeater` do not span all columns by default.
- **Use `Select::make('author_id')->relationship('author', 'name')` for BelongsTo fields.** `BelongsToSelect` does not exist in v4.
- **`Repeater` uses `->schema()`, not `->fields()`.**
- **Never add `->dehydrated(false)` to fields that need to be saved.** It strips the value from form state before `->action()` or the save handler runs. Only use it for helper/UI-only fields.
- **Use correct property types when overriding `Page`, `Resource`, and `Widget` properties.** These properties have union types or changed modifiers that must be preserved:
  - `$navigationIcon`: `protected static string | BackedEnum | null` (not `?string`)
  - `$navigationGroup`: `protected static string | UnitEnum | null` (not `?string`)
  - `$view`: `protected string` (not `protected static string`) on `Page` and `Widget` classes

</laravel-boost-guidelines>
