<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\App;
use Spatie\Browsershot\Browsershot;
use Spatie\LaravelPdf\Facades\Pdf;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url') . "/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        // Implicitly grant 'Super Admin' role all permissions
        // This works in the app by using gate-related functions like auth()->user->can() and @can()
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        // start loadMigrationsFrom
        $migrationsPath = database_path('migrations');

        $directories = glob($migrationsPath . '/*', GLOB_ONLYDIR);

        $paths = array_merge([$migrationsPath], $directories);

        $this->loadMigrationsFrom($paths);
        // end loadMigrationsFrom

        //
        // Pdf::default()->withBrowsershot(function (Browsershot $b) {
        //     if ($chrome = config('services.pdf.chrome_path')) {
        //         $b->setChromePath($chrome);
        //     }

        //     if (App::environment('production')) {
        //         $userDataDir = config('services.pdf.chrome_user_data_dir');

        //         $b->addChromiumArguments([
        //             'no-sandbox',
        //             'disable-dev-shm-usage',
        //             "user-data-dir={$userDataDir}",
        //             'disable-crash-reporter',
        //             'no-first-run',
        //         ]);
        //     }
        // });
    }
}
