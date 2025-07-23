<?php

namespace App\Events;

use App\Models\SystemConfiguration;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConfigurationChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The configuration that was changed.
     */
    public SystemConfiguration $configuration;

    /**
     * The type of change (created, updated, deleted).
     */
    public string $changeType;

    /**
     * The old values (for updates).
     */
    public ?array $oldValues;

    /**
     * Create a new event instance.
     */
    public function __construct(SystemConfiguration $configuration, string $changeType, ?array $oldValues = null)
    {
        $this->configuration = $configuration;
        $this->changeType = $changeType;
        $this->oldValues = $oldValues;
    }
}
