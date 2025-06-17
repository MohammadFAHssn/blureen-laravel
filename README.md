# blureen-laravel

> PHP 8.4.6 (cli) (built: Apr 9 2025 09:45:15) (ZTS Visual C++ 2022 x64)
> Copyright (c) The PHP Group
> Zend Engine v4.4.6, Copyright (c) Zend Technologies

> Composer version 2.8.8 2025-04-04 16:56:46

-   make the secrets/jwt direction and go to that dir and run in git bash:

```
openssl genrsa -out private.pem 2048
openssl rsa -in private.pem -pubout -out public.pem
```

-   put these in your .env file and set your path:

```dotenv
JWT_ALGO=RS256

JWT_PUBLIC_KEY="file://your-path-to-project/secrets/jwt/public.pem"
JWT_PRIVATE_KEY="file://your-path-to-project/secrets/jwt/private.pem"
```

-   run in terminal:

```bash
composer install
```

```bash
php artisan jwt:secret
```

```bash
php artisan key:generate
```

-   define your database connection settings in your .env file

```dotenv
DB_DATABASE=yourDatabaseName
DB_USERNAME=yourUserName
DB_PASSWORD=yourPassword
```

-   run in terminal:

```bash
php artisan migrate
```

-   change the locale in .env file

```dotenv
APP_LOCALE=fa
```

-   set `FRONTEND_URL` in your .env file, for example:

```dotenv
FRONTEND_URL="http://localhost:3000"
```

-   run in terminal:

```bash
php artisan permission:cache-reset
php artisan optimize:clear
php artisan config:clear
php artisan optimize
php artisan clear-compiled
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer dump-autoload
```

```bash
php artisan serve
```

test5
