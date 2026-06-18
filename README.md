# EasyWords App

![Logo](http://easywordsapp.ru/images/easywords-full.png)


A SaaS vocabulary learning platform where users can save words through mobile or web interface, study them using flashcards, and get AI-powered usage examples.

## Tech Stack

- **Backend**: Laravel 12 with PHP 8.3+
- **Frontend**: React 18+ with TypeScript, Inertia.js
- **Styling**: Tailwind CSS with shadcn/ui components
- **API**: JSON:API specification compliant
- **Admin Panel**: Filament 4
- **Authentication**: Laravel Sanctum
- **Testing**: PHPUnit with strict coverage requirements

## Features

- **Word Management**: Save words with translations and definitions
- **Flashcard Learning**: Flip-card interface for active recall practice
- **AI-Powered Examples**: Context-aware usage examples for each word
- **Multi-Platform**: Web interface and mobile app with shared backend
- **Admin Panel**: Comprehensive administration interface with Filament
- **User Statistics**: Detailed learning statistics and progress tracking
- **Word Sharing**: Share words with other users
- **Sample Import**: Import pre-defined word samples by language

## Project Structure

```
easy_words_app/
├── app/
│   ├── Actions/          # Business logic (Laravel Actions)
│   ├── Data/            # Laravel Data DTOs for API responses
│   ├── Filament/        # Admin panel resources
│   ├── Http/
│   │   ├── Controllers/ # API controllers
│   │   ├── Requests/    # Form request validation
│   │   └── Middleware/ # Custom middleware
│   └── Models/          # Eloquent models
├── database/
│   ├── factories/       # Model factories
│   ├── migrations/      # Database migrations
│   └── seeders/        # Database seeders
├── resources/
│   ├── css/            # Stylesheets
│   ├── js/             # React components (FSD architecture)
│   └── views/          # Blade templates
├── routes/             # Route definitions (using attributes)
├── tests/              # PHPUnit tests
└── documentation/      # Project documentation
```

## Installation

### Prerequisites

- PHP 8.3 or higher
- Composer 2.x
- Node.js 18+ and npm/yarn
- PostgreSQL or MySQL database

### Setup

1. **Clone the repository**

```bash
git clone https://github.com/your-repo/easy-words-app.git
cd easy-words-app
```

2. **Install PHP dependencies**

```bash
composer install
```

3. **Install Node.js dependencies**

```bash
npm install
```

4. **Configure environment**

```bash
cp .env.example .env
```

Edit `.env` and set up your database credentials:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=easywords
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

5. **Generate application key**

```bash
php artisan key:generate
```

6. **Run database migrations**

```bash
php artisan migrate
```

7. **Seed the database**

```bash
php artisan db:seed
```

8. **Build frontend assets**

```bash
npm run build
```

9. **Start development server**

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

## Development

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter WordControllerTest

# Run with coverage
php artisan test --coverage
```

### Code Quality

```bash
# Format code
task format

# Run static analysis
task analyze

# Run TypeScript type check
task type-check

# Run all quality checks
task all
```

For AI features you need external claude server or subscription to polza.

### API Development

All routes are defined using PHP 8 attributes via `spatie/laravel-route-attributes`:

```php
use Spatie\RouteAttributes\Attributes\Get;

#[Get('api/v1/words', name: 'api.v1.words.index')]
public function index(): JsonResponse
{
    // ...
}
```

### API Responses

All API responses use Laravel Data objects following JSON:API specification:

```php
use App\Data\WordData;

return WordData::from($word)->toResponse();
```

### Frontend Development

The frontend follows **Feature-Sliced Design (FSD)** architecture:

```
src/
├── app/           # App initialization
├── pages/         # Inertia page components
├── widgets/       # Complex composite features
├── features/      # User scenarios
├── entities/      # Business entities
└── shared/        # Reusable code (ui, lib, api, types)
```

## API Documentation

The API documentation is available in OpenAPI 3.0.3 format:

- **OpenAPI Spec**: `documentation/openapi.yaml`
- **Content Type**: `application/vnd.api+json`
- **Authentication**: Bearer token (Laravel Sanctum)

### Quick API Start

1. **Create a token**

```bash
curl -X POST http://localhost:8000/api/v1/token \
  -H "Content-Type: application/vnd.api+json" \
  -d '{"email":"user@example.com","password":"password"}'
```

2. **Use the token**

```bash
curl -X GET http://localhost:8000/api/v1/words \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/vnd.api+json"
```

## Screenshots

Here you can check some screenshot of this app

![App Screenshot](https://i.imgur.com/mUVQEJA.png)
![App Screenshot](https://i.imgur.com/Fk3LnPH.png)
![App Screenshot](https://i.imgur.com/m7SVj1y.png)
![App Screenshot](https://i.imgur.com/Y4cjXJe.png)
![App Screenshot](https://i.imgur.com/qTf2Htx.png)
![App Screenshot](https://i.imgur.com/06Fa4Xz.png)

## FAQ

#### Do you have mobile app?

For learning words through smartphones you can use PWA. Just open in Google Chrome you domain through HTTPS and install your website as an app.

Also I have a mobile application for android. You can download and install APK using this link: [https://easywordsapp.ru/apps/flutter_apk/app.apk](https://easywordsapp.ru/apps/flutter_apk/app.apk)

For more information visit repository of mobile application: [https://github.com/semelyanov86/easywords-native](https://github.com/semelyanov86/easywords-native)

#### Can I connect multiple users to my app?

Yes, it is support multiuser functionality. But for now registration from frontend is not supported. You can create new users through admin page.

#### Can I add support for new language?

Yes, just add new language code in `config/app.php` file, `supported_languages` array.

For example:
````php
    'supported_languages' => [
        'DE', 'EN', 'ES'
    ],
````
Then you will need to create file in folder `database/seeders/samples/ES.php`

This file should contain array with sample data of most popular words.

Then run command `php artisan db:seed --class=SampleSeeder`

Using this steps you can see new language - ES with sample data which you can import.

## Code Style Guidelines

### PHP

- **Strict types**: Every file must have `declare(strict_types=1);`
- **Purpose docblocks**: Required for all classes under `app/`
- **Type safety**: Maximum PHPStan level, no mixed types
- **Action classes**: Prefer verb-named Actions over Services
- **No dependency injection**: Use Facades, Real-Time Facades, or `app()`
- **Route attributes**: Define routes via attributes, not in route files

### TypeScript/React

- **Strict TypeScript**: No `any`, no `@ts-ignore`
- **FSD architecture**: Organize by features, not file types
- **Shadcn/ui**: Use as component foundation
- **Accessibility**: Semantic HTML, ARIA labels, focus states

## Testing Philosophy

- **Strict coverage**: All Actions, Models, and API endpoints must have tests
- **Test structure**: Mirror `./app` structure 1:1 when possible
- **JSON:API compliance**: API tests must verify spec adherence
- **Parallel safety**: Tests must not share fixed file paths
- **AI integrations**: Mock external services properly

## Deployment

### Production Checklist

- [ ] Set `APP_ENV=production` and `APP_DEBUG=false`
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `php artisan view:cache`
- [ ] Build production assets: `npm run build`
- [ ] Set up database backups
- [ ] Configure queue workers
- [ ] Set up monitoring and logging

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Run `task all` to ensure quality checks pass
5. Commit your changes (`git commit -m 'Add amazing feature'`)
6. Push to the branch (`git push origin feature/amazing-feature`)
7. Open a Pull Request

### Pull Request Requirements

- All tests must pass
- Code must pass PHPStan maximum level
- TypeScript must have no errors
- New features must include tests
- API changes must maintain JSON:API compliance
- Update documentation as needed

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support, email support@easywordsapp.ru or open an issue on GitHub.

## Acknowledgments

- [Laravel](https://laravel.com/) - The PHP framework
- [Inertia.js](https://inertiajs.com/) - The modern monolith
- [React](https://react.dev/) - The React library
- [Tailwind CSS](https://tailwindcss.com/) - The utility-first CSS framework
- [shadcn/ui](https://ui.shadcn.com/) - Beautiful UI components
- [Filament](https://filamentphp.com/) - The elegant admin panel
