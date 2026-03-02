<?php

namespace Garest\ApiGuard\Console\Commands;

use Carbon\Carbon;
use Garest\ApiGuard\Enums\KeyType;
use Illuminate\Console\Command;
use Garest\ApiGuard\Models\JwtClient;
use Illuminate\Support\Facades\Cache;

class UpdateJwtClient extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ag:jwt-client-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update an existing JWT client (revoked, scopes, expires_at)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get client id
        $clientId = $this->ask('Enter the client id to update');

        if (!$clientId) {
            $this->error('Сlient id is required.');
            return self::FAILURE;
        }

        $client = JwtClient::clientId($clientId)->first();

        if (!$client) {
            $this->error("JWT client with client id '$clientId' not found.");
            return self::FAILURE;
        }

        $this->info("Updating client: {$client->name} ({$clientId})");
        $this->line("Current status: " . ($client->revoked ? 'REVOKED' : 'ACTIVE'));
        $this->newLine();

        // Updating status (Revoked)
        if ($this->confirm('Update revoked status?', false)) {
            $client->revoked = $this->confirm('Should the client be revoked?', $client->revoked);
        }

        // Updating Scopes
        if ($this->confirm('Update scopes?', false)) {
            $currentScopes = $client->scopes ? implode(', ', $client->scopes) : 'none';
            $scopesInput = $this->ask("Enter new scopes (comma-separated) [current: $currentScopes]");

            if ($scopesInput !== null) {
                $client->scopes = array_map('trim', explode(',', $scopesInput));
            }
        }

        // Updating Expires At
        if ($this->confirm('Update expiration date?', false)) {
            $currentExpire = $client->expires_at ? $client->expires_at->toDateTimeString() : 'never';
            $expireInput = $this->ask("Enter expiration date (YYYY-MM-DD HH:MM:SS) or 'null' to remove [current: $currentExpire]");

            if ($expireInput === 'null') {
                $client->expires_at = null;
            } elseif ($expireInput) {
                try {
                    $client->expires_at = Carbon::parse($expireInput);
                } catch (\Exception $e) {
                    $this->error('Invalid date format. Skipping expiration update.');
                }
            }
        }

        // Cache storage and clearing
        if ($client->isDirty()) {
            $client->save();

            Cache::forget(JwtClient::getCacheKey(['client_id' => $clientId]));

            $this->info('✅ JWT client updated successfully.');
        } else {
            $this->comment('No changes were made.');
        }

        return self::SUCCESS;
    }
}
