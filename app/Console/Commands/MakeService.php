<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Filesystem\Filesystem;

class MakeService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:service {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a new service class';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $path = app_path("Services/{$name}.php");

        if (file_exists($path)) {
            $this->error("Service already exists!");
            return;
        }

        (new Filesystem)->ensureDirectoryExists(app_path('Services'));

        file_put_contents($path, $this->generateServiceContent($name));
    }

    protected function generateServiceContent($name)
    {
        return <<<PHP
        <?php

        namespace App\Services;

        class {$name}
        {
            //
        }

        PHP;
    }
}
