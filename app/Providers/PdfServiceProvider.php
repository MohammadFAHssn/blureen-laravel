<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\Browsershot\Browsershot;

class PdfServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $base = storage_path('chrome');

        Pdf::default()->withBrowsershot(function (Browsershot $b) use ($base) {
            $b->setChromePath(env('LARAVEL_PDF_CHROME_PATH'));

            $b->addChromiumArguments([
                'headless=new',
                'no-sandbox',
                'disable-setuid-sandbox',
                'disable-dev-shm-usage',
                'disable-gpu',
                'user-data-dir=' . $base . '/user-data',
                'data-path=' . $base . '/data-path',
                'disk-cache-dir=' . $base . '/cache',
                'crash-dumps-dir=' . $base . '/crashpad',
                'disable-crash-reporter',
            ]);

            $b->setEnvironmentOptions([
                'HOME' => $base,
                'XDG_CACHE_HOME' => $base . '/cache',
                'XDG_CONFIG_HOME' => $base . '/user-data',
            ]);
        });
    }
}
