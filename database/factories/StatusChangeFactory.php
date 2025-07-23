<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;

class StatusChangeFactory extends Factory
{
    public function definition(): array
    {
        $statuses = ['available', 'sold', 'reserved', 'cancelled'];
        $fromStatus = fake()->randomElement($statuses);
        $toStatus = fake()->randomElement(array_diff($statuses, [$fromStatus]));

        return [
            'project_id' => Project::factory(),
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'reason' => fake()->sentence(),
            'changed_by' => Admin::factory(),
            'changed_at' => fake()->dateTime(),
        ];
    }
}
