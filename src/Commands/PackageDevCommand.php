<?php

namespace QuickerFaster\LaravelUI\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class PackageDevCommand extends Command
{
    protected $signature = 'qf-laravel-ui:dev 
                            {--build : Run vite build instead of dev} 
                            {--port= : Specify dev server port} 
                            {--watch : Run build in watch mode}';

    protected $description = 'Run Vite for the package (dev or build mode).';

    public function handle()
    {
        $basePath    = base_path();
        $packagePath = base_path('vendor/quicker-faster/laravel-ui');
        $viteConfig  = $packagePath.'/vite.config.js';

        // 1. Ensure node_modules exists in host app
        if (! is_dir($basePath.'/node_modules')) {
            $this->warn('node_modules not found. Installing...');
            $this->runProcess(['npm', 'install'], $basePath);
        }

        // 2. Build command dynamically
        $command = ['npm', 'run'];

        if ($this->option('build')) {
            $command[] = 'build';
            $command[] = '--';
            $command[] = '--config';
            $command[] = $viteConfig;

            if ($this->option('watch')) {
                $command[] = '--watch';
            }
        } else {
            $command[] = 'dev';
            $command[] = '--';
            $command[] = '--config';
            $command[] = $viteConfig;

            // 👇 ensure dev server port is applied
            $port = $this->option('port') ?: 5174; // default 5174
            $command[] = '--port';
            $command[] = $port;
        }

        // 3. Run the command
        $this->info('Running: ' . implode(' ', $command));
        return $this->runProcess($command, $basePath);
    }

    protected function runProcess(array $command, string $cwd = null)
    {
        $basePath = base_path();

        $process = new Process(
            $command,
            $cwd ?: $basePath, // run in host app root
            [
                'APP_BASE_PATH' => $basePath,
            ]
        );

        if (Process::isTtySupported()) {
            $process->setTty(true);
        }

        $process->setTimeout(null);
        $process->setIdleTimeout(null);

        $process->run(function ($type, $buffer) {
            echo $buffer;
        });

        return $process->isSuccessful()
            ? Command::SUCCESS
            : Command::FAILURE;
    }
}



/*Usage
With this, running:

php artisan qf-laravel-ui:dev


➡ starts dev server on 5174.

php artisan qf-laravel-ui:dev --port=5175


➡ starts on 5175.

php artisan qf-laravel-ui:dev --build


➡ builds to public/vendor/qf.

# run vite build with watch mode
php artisan qf-laravel-ui:dev --build --watch
*/
