<?php

namespace Garest\ApiGuard\Commands;

use Carbon\Carbon;
use Garest\ApiGuard\Cache\HmacCacheKey;
use Illuminate\Console\Command;
use Garest\ApiGuard\Models\HmacKey;
use Illuminate\Support\Facades\Cache;

class UpdateHmacKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ag:hmac-key-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update an existing HMAC access key (revoked, scopes, expires_at)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get access_key
        $accessKey = $this->ask('Enter the Access Key to update');

        if (!$accessKey) {
            $this->error('Access Key is required.');
            return self::FAILURE;
        }

        $hmacKey = HmacKey::accessKey($accessKey)->first();

        if (!$hmacKey) {
            $this->error("HMAC key with Access Key '$accessKey' not found.");
            return self::FAILURE;
        }

        $this->info("Updating key: {$hmacKey->name} ({$accessKey})");
        $this->line("Current status: " . ($hmacKey->revoked ? 'REVOKED' : 'ACTIVE'));
        $this->newLine();

        // Updating status (Revoked)
        if ($this->confirm('Update revoked status?', false)) {
            $hmacKey->revoked = $this->confirm('Should the key be revoked?', $hmacKey->revoked);
        }

        // Updating Scopes
        if ($this->confirm('Update scopes?', false)) {
            $currentScopes = $hmacKey->scopes ? implode(', ', $hmacKey->scopes) : 'none';
            $scopesInput = $this->ask("Enter new scopes (comma-separated) [current: $currentScopes]");

            if ($scopesInput !== null) {
                $hmacKey->scopes = array_map('trim', explode(',', $scopesInput));
            }
        }

        // Updating Expires At
        if ($this->confirm('Update expiration date?', false)) {
            $currentExpire = $hmacKey->expires_at ? $hmacKey->expires_at->toDateTimeString() : 'never';
            $expireInput = $this->ask("Enter expiration date (YYYY-MM-DD HH:MM:SS) or 'null' to remove [current: $currentExpire]");

            if ($expireInput === 'null') {
                $hmacKey->expires_at = null;
            } elseif ($expireInput) {
                try {
                    $hmacKey->expires_at = Carbon::parse($expireInput);
                } catch (\Exception $e) {
                    $this->error('Invalid date format. Skipping expiration update.');
                }
            }
        }

        // Cache storage and clearing
        if ($hmacKey->isDirty()) {
            $hmacKey->save();

            Cache::forget(HmacCacheKey::accessKey($accessKey));

            $this->info('✅ HMAC key updated successfully.');
        } else {
            $this->comment('No changes were made.');
        }

        return self::SUCCESS;
    }
}
