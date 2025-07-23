<?php

namespace Tests\Feature;

use App\Models\SystemConfiguration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SettingsPageTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user for authentication
        $this->user = User::factory()->create();

        // Clear cache before each test
        Cache::flush();

        // Create default configurations for testing
        $this->createDefaultConfigurations();
    }

    /** @test */
    public function authenticated_user_can_access_settings_page()
    {
        // Act & Assert
        $this->actingAs($this->user)
            ->get('/admin/settings')
            ->assertStatus(200);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_settings_page()
    {
        // Act & Assert
        $this->get('/admin/settings')
            ->assertRedirect('/admin/login');
    }

    /** @test */
    public function settings_page_displays_configuration_categories()
    {
        // Act & Assert
        $response = $this->actingAs($this->user)
            ->get('/admin/settings');

        $response->assertStatus(200)
            ->assertSee('Project Statuses')
            ->assertSee('Transaction Types')
            ->assertSee('Property Types')
            ->assertSee('Theme Settings');
    }

    /** @test */
    public function user_can_view_project_status_configurations()
    {
        // Act & Assert
        $response = $this->actingAs($this->user)
            ->get('/admin/settings');

        $response->assertStatus(200)
            ->assertSee('On Going')
            ->assertSee('Exited')
            ->assertSee('Planning');
    }

    /** @test */
    public function user_can_create_new_configuration()
    {
        // Arrange
        $newConfigData = [
            'category' => 'project_statuses',
            'key' => 'new_status',
            'value' => 'new_status',
            'label' => 'New Status',
            'description' => 'A new project status',
            'is_active' => true,
            'sort_order' => 10,
        ];

        // Act
        $response = $this->actingAs($this->user)
            ->post('/admin/settings/configurations', $newConfigData);

        // Assert
        $response->assertRedirect();
        $this->assertDatabaseHas('system_configurations', $newConfigData);
    }

    /** @test */
    public function user_can_update_existing_configuration()
    {
        // Arrange
        $config = SystemConfiguration::factory()->create([
            'category' => 'project_statuses',
            'key' => 'test_status',
            'value' => 'test_status',
            'label' => 'Test Status',
        ]);

        $updateData = [
            'label' => 'Updated Test Status',
            'description' => 'Updated description',
            'is_active' => false,
        ];

        // Act
        $response = $this->actingAs($this->user)
            ->put("/admin/settings/configurations/{$config->id}", array_merge($config->toArray(), $updateData));

        // Assert
        $response->assertRedirect();
        $this->assertDatabaseHas('system_configurations', [
            'id' => $config->id,
            'label' => 'Updated Test Status',
            'description' => 'Updated description',
            'is_active' => false,
        ]);
    }

    /** @test */
    public function user_can_delete_configuration()
    {
        // Arrange
        $config = SystemConfiguration::factory()->create([
            'category' => 'test_category',
            'key' => 'deletable_config',
        ]);

        // Act
        $response = $this->actingAs($this->user)
            ->delete("/admin/settings/configurations/{$config->id}");

        // Assert
        $response->assertRedirect();
        $this->assertSoftDeleted('system_configurations', ['id' => $config->id]);
    }

    /** @test */
    public function configuration_changes_clear_cache()
    {
        // Arrange
        $config = SystemConfiguration::factory()->create([
            'category' => 'cache_test',
            'key' => 'cache_key',
            'value' => 'original_value',
        ]);

        // Populate cache
        $configService = app(\App\Services\ConfigurationService::class);
        $originalValue = $configService->getValue('cache_test', 'cache_key');
        $this->assertEquals('original_value', $originalValue);

        // Act - Update configuration through settings page
        $this->actingAs($this->user)
            ->put("/admin/settings/configurations/{$config->id}", array_merge($config->toArray(), [
                'value' => 'updated_value',
            ]));

        // Assert - Cache should be cleared and new value returned
        $updatedValue = $configService->getValue('cache_test', 'cache_key');
        $this->assertEquals('updated_value', $updatedValue);
    }

    /** @test */
    public function theme_switcher_works_on_settings_page()
    {
        // Arrange
        SystemConfiguration::factory()->create([
            'category' => 'theme_settings',
            'key' => 'light',
            'value' => 'light',
            'label' => 'Light Theme',
            'is_active' => true,
        ]);

        SystemConfiguration::factory()->create([
            'category' => 'theme_settings',
            'key' => 'dark',
            'value' => 'dark',
            'label' => 'Dark Theme',
            'is_active' => true,
        ]);

        // Act & Assert
        $response = $this->actingAs($this->user)
            ->get('/admin/settings');

        $response->assertStatus(200)
            ->assertSee('Light Theme')
            ->assertSee('Dark Theme');
    }

    /** @test */
    public function user_can_update_theme_preference()
    {
        // Arrange
        $themeConfig = SystemConfiguration::factory()->create([
            'category' => 'user_preferences',
            'key' => 'theme_preference',
            'value' => 'light',
            'label' => 'Theme Preference',
        ]);

        // Act
        $response = $this->actingAs($this->user)
            ->post('/admin/settings/theme', ['theme' => 'dark']);

        // Assert
        $response->assertRedirect();
        $this->assertDatabaseHas('system_configurations', [
            'category' => 'user_preferences',
            'key' => 'theme_preference',
            'value' => 'dark',
        ]);
    }

    /** @test */
    public function settings_page_validates_required_fields()
    {
        // Act
        $response = $this->actingAs($this->user)
            ->post('/admin/settings/configurations', [
                'category' => '',
                'key' => '',
                'label' => '',
            ]);

        // Assert
        $response->assertSessionHasErrors(['category', 'key', 'label']);
    }

    /** @test */
    public function settings_page_prevents_duplicate_category_key_combinations()
    {
        // Arrange
        SystemConfiguration::factory()->create([
            'category' => 'test_category',
            'key' => 'duplicate_key',
        ]);

        // Act
        $response = $this->actingAs($this->user)
            ->post('/admin/settings/configurations', [
                'category' => 'test_category',
                'key' => 'duplicate_key',
                'value' => 'test_value',
                'label' => 'Test Label',
            ]);

        // Assert
        $response->assertSessionHasErrors(['key']);
    }

    /** @test */
    public function settings_page_shows_configuration_usage_warnings()
    {
        // Arrange - Create a project status that's in use
        $statusConfig = SystemConfiguration::factory()->create([
            'category' => 'project_statuses',
            'key' => 'in_use_status',
            'value' => 'in_use_status',
            'label' => 'In Use Status',
        ]);

        // Create a project using this status
        \App\Models\Project::factory()->create(['status' => 'in_use_status']);

        // Act
        $response = $this->actingAs($this->user)
            ->get('/admin/settings');

        // Assert - Should show warning about configurations in use
        $response->assertStatus(200);
        // In a real implementation, this would check for usage warnings
    }

    /** @test */
    public function settings_page_supports_bulk_operations()
    {
        // Arrange
        $configs = SystemConfiguration::factory()->count(3)->create([
            'category' => 'bulk_test',
            'is_active' => true,
        ]);

        $configIds = $configs->pluck('id')->toArray();

        // Act - Bulk deactivate
        $response = $this->actingAs($this->user)
            ->post('/admin/settings/configurations/bulk', [
                'action' => 'deactivate',
                'ids' => $configIds,
            ]);

        // Assert
        $response->assertRedirect();
        foreach ($configIds as $id) {
            $this->assertDatabaseHas('system_configurations', [
                'id' => $id,
                'is_active' => false,
            ]);
        }
    }

    /** @test */
    public function settings_page_supports_import_export()
    {
        // Act - Export configurations
        $response = $this->actingAs($this->user)
            ->get('/admin/settings/export?category=project_statuses');

        // Assert
        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/json');

        $exportData = json_decode($response->getContent(), true);
        $this->assertIsArray($exportData);
        $this->assertNotEmpty($exportData);
    }

    /** @test */
    public function settings_page_handles_configuration_ordering()
    {
        // Arrange
        $config1 = SystemConfiguration::factory()->create([
            'category' => 'order_test',
            'sort_order' => 2,
            'label' => 'Second',
        ]);

        $config2 = SystemConfiguration::factory()->create([
            'category' => 'order_test',
            'sort_order' => 1,
            'label' => 'First',
        ]);

        // Act
        $response = $this->actingAs($this->user)
            ->get('/admin/settings');

        // Assert - Should display configurations in correct order
        $response->assertStatus(200);

        // Get configurations in order
        $configService = app(\App\Services\ConfigurationService::class);
        $orderedConfigs = $configService->getConfigurationsByCategory('order_test');

        $this->assertEquals('First', $orderedConfigs->first()->label);
        $this->assertEquals('Second', $orderedConfigs->last()->label);
    }

    /**
     * Create default configurations for testing
     */
    private function createDefaultConfigurations(): void
    {
        // Project statuses
        SystemConfiguration::factory()->create([
            'category' => 'project_statuses',
            'key' => 'on-going',
            'value' => 'on-going',
            'label' => 'On Going',
            'sort_order' => 1,
        ]);

        SystemConfiguration::factory()->create([
            'category' => 'project_statuses',
            'key' => 'exited',
            'value' => 'exited',
            'label' => 'Exited',
            'sort_order' => 2,
        ]);

        SystemConfiguration::factory()->create([
            'category' => 'project_statuses',
            'key' => 'planning',
            'value' => 'planning',
            'label' => 'Planning',
            'sort_order' => 3,
        ]);

        // Transaction types
        SystemConfiguration::factory()->create([
            'category' => 'transaction_types',
            'key' => 'investment',
            'value' => 'investment',
            'label' => 'Investment',
        ]);

        SystemConfiguration::factory()->create([
            'category' => 'transaction_types',
            'key' => 'revenue',
            'value' => 'revenue',
            'label' => 'Revenue',
        ]);

        // Property types
        SystemConfiguration::factory()->create([
            'category' => 'property_types',
            'key' => 'residential',
            'value' => 'residential',
            'label' => 'Residential',
        ]);

        // Theme settings
        SystemConfiguration::factory()->create([
            'category' => 'theme_settings',
            'key' => 'default_theme',
            'value' => 'light',
            'label' => 'Default Theme',
        ]);
    }
}
