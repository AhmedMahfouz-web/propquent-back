<?php

namespace Tests\Unit\Services;

use App\Models\SystemConfiguration;
use App\Services\ConfigurationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ConfigurationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ConfigurationService $configurationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configurationService = app(ConfigurationService::class);

        // Clear cache before each test
        Cache::flush();
    }

    /** @test */
    public function it_can_get_configurations_by_category()
    {
        // Arrange
        SystemConfiguration::factory()->create([
            'category' => 'test_category',
            'key' => 'test_key_1',
            'value' => 'test_value_1',
            'label' => 'Test Label 1',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        SystemConfiguration::factory()->create([
            'category' => 'test_category',
            'key' => 'test_key_2',
            'value' => 'test_value_2',
            'label' => 'Test Label 2',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        // Act
        $configurations = $this->configurationService->getConfigurationsByCategory('test_category');

        // Assert
        $this->assertCount(2, $configurations);
        $this->assertEquals('test_key_1', $configurations->first()->key);
        $this->assertEquals('test_key_2', $configurations->last()->key);
    }

    /** @test */
    public function it_caches_configurations_by_category()
    {
        // Arrange
        SystemConfiguration::factory()->create([
            'category' => 'test_category',
            'key' => 'test_key',
            'value' => 'test_value',
            'label' => 'Test Label',
            'is_active' => true,
        ]);

        // Act - First call should hit database
        $firstCall = $this->configurationService->getConfigurationsByCategory('test_category');

        // Delete the record from database
        SystemConfiguration::where('category', 'test_category')->delete();

        // Second call should return cached result
        $secondCall = $this->configurationService->getConfigurationsByCategory('test_category');

        // Assert
        $this->assertCount(1, $firstCall);
        $this->assertCount(1, $secondCall); // Should still return cached result
        $this->assertEquals($firstCall->first()->key, $secondCall->first()->key);
    }

    /** @test */
    public function it_can_get_configuration_options()
    {
        // Arrange
        SystemConfiguration::factory()->create([
            'category' => 'test_options',
            'key' => 'option_1',
            'value' => 'value_1',
            'label' => 'Option 1',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        SystemConfiguration::factory()->create([
            'category' => 'test_options',
            'key' => 'option_2',
            'value' => 'value_2',
            'label' => 'Option 2',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        // Act
        $options = $this->configurationService->getOptions('test_options');

        // Assert
        $this->assertIsArray($options);
        $this->assertArrayHasKey('option_1', $options);
        $this->assertArrayHasKey('option_2', $options);
        $this->assertEquals('Option 1', $options['option_1']);
        $this->assertEquals('Option 2', $options['option_2']);
    }

    /** @test */
    public function it_can_get_single_configuration_value()
    {
        // Arrange
        SystemConfiguration::factory()->create([
            'category' => 'test_single',
            'key' => 'single_key',
            'value' => 'single_value',
            'label' => 'Single Label',
            'is_active' => true,
        ]);

        // Act
        $value = $this->configurationService->getValue('test_single', 'single_key');

        // Assert
        $this->assertEquals('single_value', $value);
    }

    /** @test */
    public function it_returns_default_value_when_configuration_not_found()
    {
        // Act
        $value = $this->configurationService->getValue('non_existent', 'non_existent', 'default_value');

        // Assert
        $this->assertEquals('default_value', $value);
    }

    /** @test */
    public function it_can_update_existing_configuration()
    {
        // Arrange
        $config = SystemConfiguration::factory()->create([
            'category' => 'test_update',
            'key' => 'update_key',
            'value' => 'original_value',
            'label' => 'Update Label',
            'is_active' => true,
        ]);

        // Act
        $result = $this->configurationService->updateConfiguration('test_update', 'update_key', 'updated_value');

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('system_configurations', [
            'id' => $config->id,
            'value' => 'updated_value',
        ]);
    }

    /** @test */
    public function it_returns_false_when_updating_non_existent_configuration()
    {
        // Act
        $result = $this->configurationService->updateConfiguration('non_existent', 'non_existent', 'new_value');

        // Assert
        $this->assertFalse($result);
    }

    /** @test */
    public function it_can_create_new_configuration()
    {
        // Arrange
        $data = [
            'category' => 'new_category',
            'key' => 'new_key',
            'value' => 'new_value',
            'label' => 'New Label',
            'description' => 'New Description',
            'is_active' => true,
            'sort_order' => 1,
        ];

        // Act
        $config = $this->configurationService->createConfiguration($data);

        // Assert
        $this->assertInstanceOf(SystemConfiguration::class, $config);
        $this->assertDatabaseHas('system_configurations', $data);
    }

    /** @test */
    public function it_can_delete_configuration()
    {
        // Arrange
        $config = SystemConfiguration::factory()->create([
            'category' => 'test_delete',
            'key' => 'delete_key',
            'value' => 'delete_value',
            'label' => 'Delete Label',
            'is_active' => true,
        ]);

        // Act
        $result = $this->configurationService->deleteConfiguration('test_delete', 'delete_key');

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseMissing('system_configurations', [
            'id' => $config->id,
        ]);
    }

    /** @test */
    public function it_returns_false_when_deleting_non_existent_configuration()
    {
        // Act
        $result = $this->configurationService->deleteConfiguration('non_existent', 'non_existent');

        // Assert
        $this->assertFalse($result);
    }

    /** @test */
    public function it_clears_cache_when_configuration_is_updated()
    {
        // Arrange
        $config = SystemConfiguration::factory()->create([
            'category' => 'cache_test',
            'key' => 'cache_key',
            'value' => 'original_value',
            'label' => 'Cache Label',
            'is_active' => true,
        ]);

        // First call to populate cache
        $originalValue = $this->configurationService->getValue('cache_test', 'cache_key');
        $this->assertEquals('original_value', $originalValue);

        // Act - Update configuration
        $this->configurationService->updateConfiguration('cache_test', 'cache_key', 'updated_value');

        // Assert - Cache should be cleared and new value returned
        $updatedValue = $this->configurationService->getValue('cache_test', 'cache_key');
        $this->assertEquals('updated_value', $updatedValue);
    }

    /** @test */
    public function it_can_get_all_categories()
    {
        // Arrange
        SystemConfiguration::factory()->create(['category' => 'category_1']);
        SystemConfiguration::factory()->create(['category' => 'category_2']);
        SystemConfiguration::factory()->create(['category' => 'category_1']); // Duplicate category

        // Act
        $categories = $this->configurationService->getAllCategories();

        // Assert
        $this->assertCount(2, $categories);
        $this->assertContains('category_1', $categories);
        $this->assertContains('category_2', $categories);
    }

    /** @test */
    public function it_can_warm_cache_for_categories()
    {
        // Arrange
        SystemConfiguration::factory()->create([
            'category' => 'warm_test_1',
            'key' => 'key_1',
            'value' => 'value_1',
            'label' => 'Label 1',
            'is_active' => true,
        ]);

        SystemConfiguration::factory()->create([
            'category' => 'warm_test_2',
            'key' => 'key_2',
            'value' => 'value_2',
            'label' => 'Label 2',
            'is_active' => true,
        ]);

        // Act
        $warmed = $this->configurationService->warmCacheForCategories(['warm_test_1', 'warm_test_2']);

        // Assert
        $this->assertCount(2, $warmed);
        $this->assertContains('warm_test_1', $warmed);
        $this->assertContains('warm_test_2', $warmed);

        // Verify cache is populated by checking if data is still available after database deletion
        SystemConfiguration::where('category', 'warm_test_1')->delete();
        $cachedData = $this->configurationService->getConfigurationsByCategory('warm_test_1');
        $this->assertCount(1, $cachedData);
    }

    /** @test */
    public function it_handles_cache_warming_errors_gracefully()
    {
        // Act - Try to warm cache for non-existent category
        $warmed = $this->configurationService->warmCacheForCategories(['non_existent_category']);

        // Assert - Should not fail and return empty array
        $this->assertIsArray($warmed);
    }

    /** @test */
    public function it_can_check_if_configuration_exists()
    {
        // Arrange
        SystemConfiguration::factory()->create([
            'category' => 'exists_test',
            'key' => 'exists_key',
            'value' => 'exists_value',
            'label' => 'Exists Label',
            'is_active' => true,
        ]);

        // Act & Assert
        $this->assertTrue($this->configurationService->exists('exists_test', 'exists_key'));
        $this->assertFalse($this->configurationService->exists('non_existent', 'non_existent'));
    }

    /** @test */
    public function it_only_returns_active_configurations()
    {
        // Arrange
        SystemConfiguration::factory()->create([
            'category' => 'active_test',
            'key' => 'active_key',
            'value' => 'active_value',
            'label' => 'Active Label',
            'is_active' => true,
        ]);

        SystemConfiguration::factory()->create([
            'category' => 'active_test',
            'key' => 'inactive_key',
            'value' => 'inactive_value',
            'label' => 'Inactive Label',
            'is_active' => false,
        ]);

        // Act
        $configurations = $this->configurationService->getConfigurationsByCategory('active_test');
        $options = $this->configurationService->getOptions('active_test');

        // Assert
        $this->assertCount(1, $configurations);
        $this->assertEquals('active_key', $configurations->first()->key);

        $this->assertCount(1, $options);
        $this->assertArrayHasKey('active_key', $options);
        $this->assertArrayNotHasKey('inactive_key', $options);
    }
}
