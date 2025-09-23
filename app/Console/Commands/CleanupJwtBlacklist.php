<?php

namespace App\Console\Commands;

use App\Models\JwtBlacklist;
use Illuminate\Console\Command;

class CleanupJwtBlacklist extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jwt:cleanup-blacklist';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired JWT tokens from blacklist';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Cleaning up expired JWT tokens from blacklist...');
        
        $deletedCount = JwtBlacklist::cleanup();
        
        $this->info("Cleaned up {$deletedCount} expired tokens from blacklist.");
        
        return Command::SUCCESS;
    }
}
