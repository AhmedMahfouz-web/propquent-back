<?php

namespace Database\Factories;

use App\Models\SystemConfiguration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SystemConfiguration>
 */
class SystemConfigurationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = SystemConfiguration::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'category' => $this->faker->randomElement([
                'project_statuses',
                'transaction_types',
                'property_types',
                'investment_types',
                'payment_methods',
                'serving_types',
            ]),
            'key' => $this->faker->unique()->slug(2),
            'value' => $this->faker->word(),
            'label' => $this->faker->words(2, true),
            'description' => $this->faker->optional()->sentence(),
            'type' => $this->faker->randomElement(['text', 'select', 'boolean', 'number']),
            'options' => $this->faker->optional()->randomElement([
                null,
                ['option1' => 'Option 1', 'option2' => 'Option 2'],
                ['min' => 0, 'max' => 100],
            ]),
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
            'sort_order' => $this->faker->numberBetween(0, 100),
        ];
    }

    /**
     * Indicate that the configuration is active.
     */
    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the configuration is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Set a specific category.
     */
    public function category(string $category): static
    {
        return $this->state(fn(array $attributes) => [
            'category' => $category,
        ]);
    }

    /**
     * Set a specific key.
     */
    public function key(string $key): static
    {
        return $this->state(fn(array $attributes) => [
            'key' => $key,
        ]);
    }

    /**
     * Set a specific value.
     */
    public function value(string $value): static
    {
        return $this->state(fn(array $attributes) => [
            'value' => $value,
        ]);
    }

    /**
     * Create a project status configuration.
     */
    public function projectStatus(): static
    {
        return $this->state(fn(array $attributes) => [
            'category' => 'project_statuses',
            'key' => $this->faker->randomElement(['on-going', 'exited', 'planning', 'completed']),
            'label' => $this->faker->randomElement(['On Going', 'Exited', 'Planning', 'Completed']),
            'type' => 'select',
        ]);
    }

    /**
     * Create a transaction type configuration.
     */
    public function transactionType(): static
    {
        return $this->state(fn(array $attributes) => [
            'category' => 'transaction_types',
            'key' => $this->faker->randomElement(['investment', 'revenue', 'expense', 'profit']),
            'label' => $this->faker->randomElement(['Investment', 'Revenue', 'Expense', 'Profit']),
            'type' => 'select',
        ]);
    }

    /**
     * Create a property type configuration.
     */
    public function propertyType(): static
    {
        return $this->state(fn(array $attributes) => [
            'category' => 'property_types',
            'key' => $this->faker->randomElement(['residential', 'commercial', 'industrial', 'land']),
            'label' => $this->faker->randomElement(['Residential', 'Commercial', 'Industrial', 'Land']),
            'type' => 'select',
        ]);
    }

    /**
     * Create an investment type configuration.
     */
    public function investmentType(): static
    {
        return $this->state(fn(array $attributes) => [
            'category' => 'investment_types',
            'key' => $this->faker->randomElement(['equity', 'debt', 'hybrid', 'mezzanine']),
            'label' => $this->faker->randomElement(['Equity', 'Debt', 'Hybrid', 'Mezzanine']),
            'type' => 'select',
        ]);
    }

    /**
     * Create with specific sort order.
     */
    public function sortOrder(int $order): static
    {
        return $this->state(fn(array $attributes) => [
            'sort_order' => $order,
        ]);
    }

    /**
     * Create with JSON options.
     */
    public function withOptions(array $options): static
    {
        return $this->state(fn(array $attributes) => [
            'options' => $options,
        ]);
    }
}
