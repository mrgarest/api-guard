<?php

namespace Garest\ApiGuard\Console\Commands;

use Illuminate\Console\Command;

class KeyGenerateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ag:key-generate {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a 32-character encryption key';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = base_path('.env');
        $envContent = file_get_contents($path);

        $keyName = 'API_GUARD_KEY';
        $key = bin2hex(random_bytes(16));

        // Checking if the key already exists
        if (str_contains($envContent, $keyName)) {
            if (!$this->confirm('The encryption key already exists. Resetting it will render all current encrypted secrets unreadable! Do you want to continue?')) {
                return;
            }

            // Replace the key
            $envContent = preg_replace("/^{$keyName}=.*/m", "{$keyName}={$key}", $envContent);
        } else {
            // If it does not exist, add it to the end of the file.
            $envContent .= "\n{$keyName}={$key}\n";
        }

        file_put_contents($path, $envContent);

        $this->info("$keyName=$key");

        return self::SUCCESS;
    }
}
