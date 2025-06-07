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
        $nameInput = $this->argument('name');
        $path = app_path("Services/{$nameInput}.php");

        $namespace = dirname('App\\Services\\' . $nameInput);

        $className = class_basename($nameInput);

        if (file_exists($path)) {
            $this->error('Service already exists!');
            return;
        }

        (new Filesystem)->ensureDirectoryExists(dirname($path));

        file_put_contents($path, $this->generateServiceContent($namespace, $className));

        $this->info("Service {$className} created successfully at {$path}.");
    }

    protected function generateServiceContent(string $namespace, string $className): string
    {
        return <<<PHP
        <?php

        namespace {$namespace};

        class {$className}
        {
            //
        }

        PHP;
    }
}

