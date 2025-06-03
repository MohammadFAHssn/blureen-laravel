<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Filesystem\Filesystem;

class MakeRepository extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:repository {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a new repository class';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $nameInput = $this->argument('name');
        $path = app_path("Repositories/{$nameInput}.php");

        $namespace = dirname('App\\Repositories\\' . $nameInput);

        $className = class_basename($nameInput);

        if (file_exists($path)) {
            $this->error("Repository already exists!");
            return;
        }

        (new Filesystem)->ensureDirectoryExists(dirname($path));

        file_put_contents($path, $this->generateRepositoryContent($namespace, $className));

        $this->info("Repository {$className} created successfully at {$path}.");
    }

    protected function generateRepositoryContent(string $namespace, string $className): string
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

