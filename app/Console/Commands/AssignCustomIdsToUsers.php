<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AssignCustomIdsToUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:assign-custom-ids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign custom IDs (inv-1, inv-2, etc.) to existing users who don\'t have them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to assign custom IDs to users...');

        $usersWithoutCustomId = User::whereNull('custom_id')->orderBy('id')->get();

        if ($usersWithoutCustomId->isEmpty()) {
            $this->info('All users already have custom IDs assigned.');
            return;
        }

        $this->info("Found {$usersWithoutCustomId->count()} users without custom IDs.");

        $progressBar = $this->output->createProgressBar($usersWithoutCustomId->count());
        $progressBar->start();

        foreach ($usersWithoutCustomId as $user) {
            $customId = $this->generateCustomId();
            $user->update(['custom_id' => $customId]);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info('Successfully assigned custom IDs to all users!');
    }

    /**
     * Generate a unique custom ID in the format inv-1, inv-2, etc.
     */
    private function generateCustomId(): string
    {
        $lastUser = DB::table('users')
            ->whereNotNull('custom_id')
            ->where('custom_id', 'like', 'inv-%')
            ->orderByRaw('CAST(SUBSTRING(custom_id, 5) AS UNSIGNED) DESC')
            ->first();

        $nextNumber = 1;
        if ($lastUser && preg_match('/inv-(\d+)/', $lastUser->custom_id, $matches)) {
            $nextNumber = (int)$matches[1] + 1;
        }

        return 'inv-' . $nextNumber;
    }
}
