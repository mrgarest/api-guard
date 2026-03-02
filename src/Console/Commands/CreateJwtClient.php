<?php

namespace Garest\ApiGuard\Console\Commands;

use Carbon\Carbon;
use Garest\ApiGuard\Models\JwtClient;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateJwtClient extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ag:jwt-client-create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new JWT Client';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //  Ask for name
        $name = $this->ask('Enter a name for this client');

        if (!$name) {
            $this->error('The name is required.');
            return self::FAILURE;
        }

        // Generate client id
        $clientIdLength = 32;
        $clientId = Str::lower(Str::random($clientIdLength));

        // Ensure uniqueness
        while (JwtClient::clientId($clientId)->exists()) {
            $clientId = Str::lower(Str::random($clientIdLength));
        }

        // Generate plain secret
        $secret = Str::random(32);

        // Ask for scopes
        $scopesInput = $this->ask('Scopes (comma-separated, optional)');
        $scopes = $scopesInput ? array_map('trim', explode(',', $scopesInput)) : [];

        // Expiration
        $expiresAt = null;
        $expireInput = $this->ask('Expiration date (YYYY-MM-DD HH:MM:SS, optional, enter to skip)');

        if ($expireInput) {
            try {
                $expiresAt = Carbon::parse($expireInput);
            } catch (\Exception $e) {
                $this->error('Invalid date format. Key will be created without expiration.');
            }
        }

        // Save to database
        JwtClient::create([
            'name' => $name,
            'client_id' => $clientId,
            'secret' => $secret,
            'revoked' => false,
            'scopes' => !empty($scopes) ? $scopes : null,
            'expires_at' => $expiresAt,
        ]);

        // Output credentials
        $this->newLine();
        $this->info('✅ JWT key created successfully.');
        $this->newLine();

        $this->table(
            ['Label', 'Value'],
            [
                ['Name', $name],
                ['Client id', $clientId],
                ['Secret Key', $secret],
                ['Scopes', !empty($scopes) ? implode(', ', $scopes) : 'none'],
                ['Expires At', $expiresAt ? $expiresAt->toDateTimeString() : 'Never'],
            ]
        );

        $this->newLine();
        $this->warn('⚠️ IMPORTANT: Copy the Secret Key now. It will NOT be shown again.');

        return self::SUCCESS;
    }
}
