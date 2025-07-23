# Project Structure

## Laravel Standard Structure

### Application Core

-   `app/` - Application logic
    -   `Console/` - Artisan commands
    -   `Http/` - Controllers, middleware, requests
    -   `Models/` - Eloquent models
    -   `Providers/` - Service providers
    -   `Filament/` - Filament admin panel resources

### Configuration & Bootstrap

-   `bootstrap/` - Framework bootstrap files
-   `config/` - Configuration files
-   `routes/` - Route definitions

### Database

-   `database/`
    -   `migrations/` - Database schema migrations
    -   `seeders/` - Database seeders
    -   `factories/` - Model factories
    -   `database.sqlite` - SQLite database file

### Frontend Assets

-   `resources/` - Views, CSS, JS source files
-   `public/` - Web-accessible files
-   `storage/` - File storage, logs, cache

### Testing

-   `tests/`
    -   `Feature/` - Feature tests
    -   `Unit/` - Unit tests

## Key Models & Relationships

### Core Entities

-   `Project` - Main entity (UUID primary key)
-   `Developer` - Property developers
-   `ProjectTransaction` - Financial transactions
-   `StatusChange` - Project status history
-   `ProjectImage` - Project images

### Relationships

-   Developer → hasMany Projects
-   Project → hasMany Transactions
-   Project → hasMany StatusChanges
-   Project → hasMany Images
-   Project → implements HasMedia (Spatie)

## Naming Conventions

### Models

-   Singular names (Project, Developer)
-   PascalCase class names
-   Use UUID for Project model, auto-increment for others

### Database

-   Snake_case table/column names
-   Plural table names (projects, developers)
-   Foreign keys follow Laravel conventions (developer_id)
-   Use enums for status fields

### Files

-   Controllers: PascalCase + "Controller" suffix
-   Models: PascalCase singular
-   Migrations: timestamp_snake_case_description
-   Use consistent naming across related files

## Configuration Notes

-   Environment variables in `.env`
-   Media library configured for images
-   JWT authentication setup
-   Filament admin panel integrated
-   Queue system configured
