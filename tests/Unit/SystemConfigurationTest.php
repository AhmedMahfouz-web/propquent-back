<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\SystemConfiguration;
use App\Services\ConfigurationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SystemConfigurationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test configurations
        SystemConfiguration::create([
            'category' => 'test_category',
            'key' => 'test_key',
            'value' => 'test_value',
            'label' => 'Test Label',
            'description' => 'Test description',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        SystemConfiguration::create([
            'category' => 'test_category',
            'key' => 'inactive_key',
            'value' => 'inactive_value',
            'label' => 'Inactive Label',
            'description' => 'Inactive description',
            'is_active' => false,
            'sort_order' => 2,
        ]);
    }

    public function test_can_create_system_configuration(): void
    {
        $config = SystemConfiguration::create([
            'category' => 'new_category',
            'key' => 'new_key',
            'value' => 'new_value',
            'label' => 'New Label',
            'description' => 'New description',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->assertDatabaseHas('system_configurations', [
            'category' => 'new_category',
            'key' => 'new_key',
            'value' => 'new_value',
        ]);
    }

    public function test_by_category_scope(): void
    {
        $configs = SystemConfiguration::byCategory('test_category')->get();

        $this->assertCount(2, $configs);
        $this->assertTrue($configs->every(fn($config) => $config->category === 'test_category'));
    }

    public function test_active_scope(): void
    {
        $configs = SystemConfiguration::active()->get();

        $this->assertTrue($configs->every(fn($config) => $config->is_active === true));
    }

    public function test_get_by_category_static_method(): void
    {
        $configs = SystemConfiguration::getByCategory('test_category');

        $this->assertCount(1, $configs); // Only active ones
        $this->assertEquals('test_key', $configs->first()->key);
    }

    public function test_get_value_static_method(): void
    {
        $value = SystemConfiguration::getValue('test_category', 'test_key');
        $this->assertEquals('test_value', $value);

        $defaultValue = SystemConfiguration::getValue('test_category', 'nonexistent_key', 'default');
        $this->assertEquals('default', $defaultValue);
    }

    public function test_get_options_static_method(): void
    {
        $options = SystemConfiguration::getOptions('test_category');

        $this->assertIsArray($options);
        $this->assertArrayHasKey('test_key', $options);
        $this->assertEquals('Test Label', $options['test_key']);
        $this->assertArrayNotHasKey('inactive_key', $options); // Inactive should not be included
    }

    public function test_configuration_service_get_value(): void
    {
        $service = new ConfigurationService();

        $value = $service->getValue('test_category', 'test_key');
        $this->assertEquals('test_value', $value);
    }

    public function test_configuration_service_get_options(): void
    {
        $service = new ConfigurationService();

        $options = $service->getOptions('test_category');
        $this->assertIsArray($options);
        $this->assertArrayHasKey('test_key', $options);
    }

    public function test_configuration_service_get_configurations_by_category(): void
    {
        $service = new ConfigurationService();

        $configs = $service->getConfigurationsByCategory('test_category');
        $this->assertCount(1, $configs); // Only active ones
    }
}
