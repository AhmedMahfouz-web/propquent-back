<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ConfigurationService;

class WarmConfigCache extends Command
{
    protected $signature = 'config:cache';
    protected $description = 'Warm up the application configuration cache.';

    protected $configService;

    public function __construct(ConfigurationService $configService)
    {
        parent::__construct();
        $this->configService = $configService;
    }

    public function handle()
    {
        $this->info('Warming up configuration cache...');
        $this->configService->warmCache($this);
        $this->info('Configuration cache has been warmed up successfully.');
    }
}
