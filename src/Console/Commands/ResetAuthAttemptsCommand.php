<?php

namespace Garest\ApiGuard\Console\Commands;

use Garest\ApiGuard\Facades\AuthAttemptLimiter;
use Illuminate\Console\Command;

class ResetAuthAttemptsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ag:reset-attempts {ip : IP user address}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset authentication attempt limits and unblock IP';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ip = $this->argument('ip');

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            $this->error("The IP address [{$ip}] is incorrect.");
            return self::FAILURE;
        }

        AuthAttemptLimiter::reset($ip);

        $this->info("Authentication attempts for IP [{$ip}] have been reset.");

        return self::SUCCESS;
    }
}
