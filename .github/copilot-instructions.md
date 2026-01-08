# Copilot Instructions - Blureen Laravel

## Project Overview

Persian/Farsi HR management system (Laravel 12, PHP 8.4) integrating with **Rayvarz** (ERP system) and **Kasra** external APIs. Uses JWT authentication with RS256 keys and Spatie permissions. All user-facing messages should be in Persian.

## Architecture Pattern: Controller → Service → Repository

Each module follows a strict layered architecture organized by domain:

```
app/
├── Http/Controllers/{Module}/     # Handle HTTP, call services, return JSON
├── Services/{Module}/             # Business logic, validation rules
├── Repositories/{Module}/         # Database operations only
├── Models/{Module}/               # Eloquent models with relationships
└── Http/Requests/{Module}/        # Form validation with Persian messages
```

**Modules**: `Base`, `Birthday`, `Commerce`, `Evaluation`, `HSE`, `Payroll`, `Survey`, `PersonnelRecords`

### Key Pattern Example

```php
// Controller: inject service, delegate business logic
public function __construct(BirthdayGiftService $service) {
    $this->birthdayGiftService = $service;
}

// Service: inject repository, implement business rules
public function __construct(BirthdayGiftRepository $repository) {
    $this->birthdayGiftRepository = $repository;
}

// Repository: direct model operations only
public function create(array $data) {
    return BirthdayGift::create($data);
}
```

## Dynamic Base Route System

The generic endpoint `GET /api/{module}/{model_name}` (handled by `BaseController` + `BaseService`) dynamically queries any model using Spatie QueryBuilder. Supports `filter`, `include`, `fields` query params. **This route must remain last in api.php**.

Permission checking uses `CheckPermission` middleware which maps URLs to permissions via `PermissionUrl` model.

## External API Integration

### Rayvarz ERP (`App\Services\Api\RayvarzService`)

-   Sync jobs via `SyncWithRayvarzJob` with queue `sync`
-   Report IDs defined in `App\Enums\Rayvarz::REPORTS`
-   Config in `config/services.php` under `rayvarz` key

### Kasra (`App\Services\Api\KasraService`)

-   User data sync via `SyncWithKasraJob`

## Authentication & Authorization

-   JWT with RS256 keys in `secrets/jwt/` directory
-   `JwtMiddleware` for protected routes
-   Spatie permissions: `->middleware('permission:read Model-Name')` or `->middleware('role:Super Admin|employee')`
-   Super Admin bypasses all permission checks

## Key Development Commands

```bash
# Development (runs server + queue + vite concurrently)
composer dev

# Testing with Pest
composer test

# Cache management after changes
php artisan permission:cache-reset
php artisan optimize:clear
php artisan config:cache && php artisan route:cache
```

## Coding Conventions

### Persian Messages

All validation and response messages must be Persian:

```php
// In FormRequest classes
public function messages() {
    return ['name.required' => 'نام الزامی است.'];
}

// In service/controller responses
return response()->json(['message' => 'عملیات با موفقیت انجام شد.'], 200);
```

### Date Handling

Use `morilog/jalali` for Persian calendar conversions:

```php
use Morilog\Jalali\Jalalian;
Jalalian::fromFormat('Ymd', $date)->toCarbon();
```

Helper functions in `app/Helpers/helpers.php`: `getJalaliMonthNameByIndex()`, `jalalianYmdDateToCarbon()`, `arabicToPersian()`

### Exceptions

Use `App\Exceptions\CustomException` for business logic errors with HTTP codes:

```php
throw new CustomException('پیام خطا به فارسی', 403);
```

### Response Format

Standard JSON response structure:

```php
return response()->json([
    'data' => $data,
    'message' => 'پیام موفقیت',
    'status' => 200
], 200);
```

## File Structure for New Features

When adding a new feature to module `{Module}`:

1. Model: `app/Models/{Module}/{ModelName}.php`
2. Repository: `app/Repositories/{Module}/{ModelName}Repository.php`
3. Service: `app/Services/{Module}/{ModelName}Service.php`
4. Controller: `app/Http/Controllers/{Module}/{ModelName}Controller.php`
5. Requests: `app/Http/Requests/{Module}/Create{ModelName}Request.php`, `Update{ModelName}Request.php`
6. Routes: Add to `routes/api.php` within appropriate prefix group

## Important Files Reference

-   [app/Services/Base/BaseService.php](app/Services/Base/BaseService.php) - Dynamic model querying
-   [app/Http/Middleware/CheckPermission.php](app/Http/Middleware/CheckPermission.php) - URL-based permission system
-   [app/Helpers/helpers.php](app/Helpers/helpers.php) - Global helper functions
-   [app/Enums/Rayvarz.php](app/Enums/Rayvarz.php) - External API report mappings
