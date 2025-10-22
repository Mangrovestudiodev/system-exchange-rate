**\*\*\*\*** command for migrate \***\*\*\*\***

-   create a request class

# php artisan make:request NameRequest

-   create a resource

# php artisan make:resource NameResource

-   create model + migration + controller (All in One Command)

# php artisan make:model Post -mc

-   start development server

# php artisan server

-   clear and cache configuration

# php artisan config:clear

# php artisan config:cache

-   cache routes for faster performance

# php artisan route:cache

-   cache views

# php artisan view:cache

-   run all migrations

# php artisan migrate

-   reset and rerun all migrations

# php artisan migrate:refresh

-   drop all tables and re-run migrations

# php artisan migrate:fresh

-   clear all caches (config, route, view, etc.)

# php artisan optimize:clear

-   create a seeder class

# php artisan make:seeder SeederName

-run all seeders

# php artisan db:seed
