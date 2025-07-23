# Technology Stack

## Core Framework

-   **Laravel 12.0** - PHP web application framework
-   **PHP 8.2+** - Required minimum version
-   **Filament 3.3** - Admin panel and form builder

## Key Dependencies

-   **JWT Auth** (`tymon/jwt-auth`) - API authentication
-   **Spatie Media Library** - File and image management
-   **SQLite** - Default database (database.sqlite)
-   **Tailwind CSS 4.0** - Styling framework
-   **Vite 6.2** - Frontend build tool

## Development Tools

-   **Laravel Pint** - Code style fixer
-   **PHPUnit** - Testing framework
-   **Laravel Sail** - Docker development environment
-   **Laravel Pail** - Log viewer
-   **Faker** - Test data generation

## Common Commands

### Development

```bash
# Start development server with all services
composer dev

# Individual services
php artisan serve          # Web server
php artisan queue:listen   # Queue worker
php artisan pail          # Log viewer
npm run dev               # Vite dev server
```

### Testing

```bash
composer test             # Run PHPUnit tests
php artisan test         # Alternative test command
```

### Database

```bash
php artisan migrate       # Run migrations
php artisan migrate:fresh # Fresh migration
php artisan db:seed      # Run seeders
```

### Code Quality

```bash
./vendor/bin/pint        # Fix code style
php artisan config:clear # Clear config cache
```

### Build

```bash
npm run build            # Production build
php artisan optimize     # Optimize for production
```

## Architecture Notes

-   Implements HasMedia interface for file handling
-   Follows Laravel's Eloquent ORM patterns
-   Uses enum types for status fields
-   Implements soft deletes and timestamps
