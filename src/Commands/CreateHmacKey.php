<?php

namespace Garest\ApiGuard\Commands;

use Carbon\Carbon;
use Garest\ApiGuard\Helper;
use Illuminate\Console\Command;
use Garest\ApiGuard\Models\HmacKey;

class CreateHmacKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ag:hmac-key-create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new HMAC access key';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //  Ask for name
        $name = $this->ask('Enter the name for this access key');

        if (!$name) {
            $this->error('The name is required.');
            return self::FAILURE;
        }

        // Generate access_key (public identifier)
        $accessKey = Helper::accessKey();

        // Ensure uniqueness (paranoia)
        while (HmacKey::accessKey($accessKey)->exists()) {
            $accessKey = Helper::accessKey();
        }

        // Generate plain secret (shown ONCE)
        $plainSecret = Helper::secret();

        // Derive HMAC
        $derivedKey = hash('sha256', $plainSecret);

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
        HmacKey::create([
            'name' => $name,
            'access_key' => $accessKey,
            'secret' => $derivedKey, // derived HMAC
            'revoked' => false,
            'scopes' => !empty($scopes) ? $scopes : null,
            'expires_at' => $expiresAt,
        ]);

        // Output credentials
        $this->newLine();
        $this->info('✅ HMAC access key created successfully.');
        $this->newLine();

        $this->table(
            ['Label', 'Value'],
            [
                ['Name', $name],
                ['Access Key (Public)', $accessKey],
                ['Secret Key (Private)', $plainSecret],
                ['Scopes', !empty($scopes) ? implode(', ', $scopes) : 'none'],
                ['Expires At', $expiresAt ? $expiresAt->toDateTimeString() : 'Never'],
            ]
        );

        $this->newLine();
        $this->warn('⚠️ IMPORTANT: Copy the Secret Key now. It will NOT be shown again.');

        return self::SUCCESS;
    }
}
