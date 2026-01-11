# Easywords App

This file is for coding agents working in this repo. Follow it literally.

## Project context

- **Easywords App (`<easywordsapp.ru>`) is a SaaS vocabulary learning platform**: Users save words through mobile or web interface, study them using flashcards, and get AI-generated usage examples.
- **Tech stack**: Laravel 12 + Inertia.js + React + TypeScript. API follows JSON:API specification. Admin panel built with Filament 4.
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
- **Inertia integration**: use `useForm`, `router`, and typed page props.
- **HTML must be tidy, valid, semantic, and accessible.**
- **Close inline tags** (`<meta />`, `<img />`, `<br />`, etc).
- **Prefer landmarks** (`header`, `nav`, `main`, `footer`) over generic wrappers.
- **Keep focus outlines.** Focus states should be visible and intentional.
- **Every input needs a `<label>`** (via `htmlFor` + `id`) unless there's a strong reason.
- **Icons:** decorative icons get `aria-hidden="true"`; informative icons need an accessible name.

### FSD Structure

```
src/
├── app/           # App initialization, providers, routing
├── pages/         # Inertia page components
├── widgets/       # Complex composite features (e.g., WordCard, FlashcardViewer)
├── features/      # User scenarios (e.g., SaveWord, StudyFlashcards, ViewExamples)
├── entities/      # Business entities (e.g., Word, User, Example)
├── shared/        # Reusable code (ui, lib, api, types)
```

### Styling

- Use shadcn/ui components as foundation.
- Prefer Tailwind utilities over custom CSS.
- If custom CSS is necessary, keep it minimal and document why.
- Extract repeated UI patterns into reusable components (don't copy/paste class strings).

### State Management

- Use Inertia's built-in state management for page data.
- Keep component state small and local.
- For shared state, prefer React Context or Zustand (document why if introduced).

## Testing (PHPUnit)

- **Strict coverage requirements**: all Actions, Models, API endpoints must have tests.
- Test files mirror `./app` structure 1:1 when possible.
    - If there is no matching `app/` file, only then place tests at the root (e.g. `./tests/Feature`) with a clear justification.
- **API tests must verify JSON:API compliance**: structure, content types, relationships.
- **Filament tests**: test custom field classes, form submissions, table filters/actions.
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
- **Sanity:** no debug helpers left behind; migrations reversible; UI remains accessible; minimal change set; JSON:API compliance verified; Data objects used for API responses; routes defined via attributes.

## Default review behavior (whenever you touch code)

- **Existence check**: for every `app/` file you create or edit, confirm it earns its existence. If it's redundant/unused/over-abstracted, prefer deleting/merging/moving it (and updating routes/usages/tests).
- **Logic check**: inside kept files, remove or simplify any code that isn't justified (dead branches, unused options, placeholder copy, unnecessary indirection).
- **Type safety**: verify strict types are used everywhere, no escape hatches without justification.
- **Test alignment**: keep tests mirrored to `app/` structure 1:1 when possible; update or delete tests alongside code changes.
- **API compliance**: verify JSON:API spec adherence for all API endpoints.
- **Data object usage**: verify API controllers return Data objects, not raw models.
- **Route attributes**: verify routes are defined via attributes on controller methods, not in route files.
- **FSD compliance**: verify frontend code follows Feature-Sliced Design principles.
- **Filament organization**: verify schemas and complex fields are properly extracted from Resources.
