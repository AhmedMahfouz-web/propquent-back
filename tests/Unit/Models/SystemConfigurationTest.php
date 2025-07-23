<?php

namespace Tests\Unit\Models;

use App\Models\SystemConfiguration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemConfigurationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fillable_attributes()
    {
        // Arrange
        $fillable = [
            'category',
            'key',
            'value',
            'label',
            'description',
            'type',
            'options',
            'is_active',
            'sort_order',
        ];

        // Act
        $model = new SystemConfiguration();

        // Assert
        $this->assertEquals($fillable, $model->getFillable());
    }

    /** @test */
    public function it_has_correct_casts()
    {
        // Arrange
        $expectedCasts = [
            'id' => 'int',
            'options' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];

        // Act
        $model = new SystemConfiguration();
        $casts = $model->getCasts();

        // Assert
        foreach ($expectedCasts as $attribute => $cast) {
            $this->assertEquals($cast, $casts[$attribute]);
        }
    }

    /** @test */
    public function it_can_scope_by_category()
    {
        // Arrange
        SystemConfiguration::factory()->create(['category' => 'test_category']);
        SystemConfiguration::factory()->create(['category' => 'other_category']);

        // Act
        $results = SystemConfiguration::byCategory('test_category')->get();

        // Assert
        $this->assertCount(1, $results);
        $this->assertEquals('test_category', $results->first()->category);
    }

    /** @test */
    public function it_can_scope_active_only()
    {
        // Arrange
        SystemConfiguration::factory()->create(['is_active' => true]);
        SystemConfiguration::factory()->create(['is_active' => false]);

        // Act
        $results = SystemConfiguration::active()->get();

        // Assert
        $this->assertCount(1, $results);
        $this->assertTrue($results->first()->is_active);
    }

    /** @test */
    public function it_can_scope_ordered()
    {
        // Arrange
        SystemConfiguration::factory()->create(['sort_order' => 3, 'label' => 'Third']);
        SystemConfiguration::factory()->create(['sort_order' => 1, 'label' => 'First']);
        SystemConfiguration::factory()->create(['sort_order' => 2, 'label' => 'Second']);

        // Act
        $results = SystemConfiguration::ordered()->get();

        // Assert
        $this->assertEquals('First', $results->first()->label);
        $this->assertEquals('Second', $results->get(1)->label);
        $this->assertEquals('Third', $results->last()->label);
    }

    /** @test */
    public function it_can_get_configurations_by_category()
    {
        // Arrange
        SystemConfiguration::factory()->create([
            'category' => 'test_category',
            'key' => 'key_1',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        SystemConfiguration::factory()->create([
            'category' => 'test_category',
            'key' => 'key_2',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        SystemConfiguration::factory()->create([
            'category' => 'other_category',
            'key' => 'key_3',
            'is_active' => true,
        ]);

        // Act
        $results = SystemConfiguration::getByCategory('test_category');

        // Assert
        $this->assertCount(2, $results);
        $this->assertEquals('key_2', $results->first()->key); // Should be ordered by sort_order
        $this->assertEquals('key_1', $results->last()->key);
    }

    /** @test */
    public function it_can_get_options_for_category()
    {
        // Arrange
        SystemConfiguration::factory()->create([
            'category' => 'options_test',
            'key' => 'option_1',
            'label' => 'Option 1',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        SystemConfiguration::factory()->create([
            'category' => 'options_test',
            'key' => 'option_2',
            'label' => 'Option 2',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        SystemConfiguration::factory()->create([
            'category' => 'options_test',
            'key' => 'option_3',
            'label' => 'Option 3',
            'is_active' => false, // Should be excluded
        ]);

        // Act
        $options = SystemConfiguration::getOptions('options_test');

        // Assert
        $this->assertIsArray($options);
        $this->assertCount(2, $options);
        $this->assertArrayHasKey('option_2', $options); // Should be first due to sort_order
        $this->assertArrayHasKey('option_1', $options);
        $this->assertArrayNotHasKey('option_3', $options); // Inactive should be excluded
        $this->assertEquals('Option 2', $options['option_2']);
        $this->assertEquals('Option 1', $options['option_1']);
    }

    /** @test */
    public function it_can_get_single_value()
    {
        // Arrange
        SystemConfiguration::factory()->create([
            'category' => 'value_test',
            'key' => 'test_key',
            'value' => 'test_value',
            'is_active' => true,
        ]);

        // Act
        $value = SystemConfiguration::getValue('value_test', 'test_key');

        // Assert
        $this->assertEquals('test_value', $value);
    }

    /** @test */
    public function it_returns_default_value_when_not_found()
    {
        // Act
        $value = SystemConfiguration::getValue('non_existent', 'non_existent', 'default_value');

        // Assert
        $this->assertEquals('default_value', $value);
    }

    /** @test */
    public function it_returns_null_when_configuration_is_inactive()
    {
        // Arrange
        SystemConfiguration::factory()->create([
            'category' => 'inactive_test',
            'key' => 'inactive_key',
            'value' => 'inactive_value',
            'is_active' => false,
        ]);

        // Act
        $value = SystemConfiguration::getValue('inactive_test', 'inactive_key', 'default_value');

        // Assert
        $this->assertEquals('default_value', $value);
    }

    /** @test */
    public function it_can_check_if_configuration_exists()
    {
        // Arrange
        SystemConfiguration::factory()->create([
            'category' => 'exists_test',
            'key' => 'exists_key',
            'is_active' => true,
        ]);

        SystemConfiguration::factory()->create([
            'category' => 'exists_test',
            'key' => 'inactive_key',
            'is_active' => false,
        ]);

        // Act & Assert
        $this->assertTrue(SystemConfiguration::configExists('exists_test', 'exists_key'));
        $this->assertFalse(SystemConfiguration::configExists('exists_test', 'inactive_key')); // Inactive should return false
        $this->assertFalse(SystemConfiguration::configExists('non_existent', 'non_existent'));
    }

    /** @test */
    public function it_has_unique_constraint_on_category_and_key()
    {
        // Arrange
        SystemConfiguration::factory()->create([
            'category' => 'unique_test',
            'key' => 'unique_key',
        ]);

        // Act & Assert
        $this->expectException(\Illuminate\Database\QueryException::class);

        SystemConfiguration::factory()->create([
            'category' => 'unique_test',
            'key' => 'unique_key',
        ]);
    }

    /** @test */
    public function it_can_handle_json_options()
    {
        // Arrange
        $options = ['option1' => 'value1', 'option2' => 'value2'];

        $config = SystemConfiguration::factory()->create([
            'category' => 'json_test',
            'key' => 'json_key',
            'options' => $options,
        ]);

        // Act
        $retrievedConfig = SystemConfiguration::find($config->id);

        // Assert
        $this->assertIsArray($retrievedConfig->options);
        $this->assertEquals($options, $retrievedConfig->options);
    }

    /** @test */
    public function it_can_handle_null_options()
    {
        // Arrange
        $config = SystemConfiguration::factory()->create([
            'category' => 'null_test',
            'key' => 'null_key',
            'options' => null,
        ]);

        // Act
        $retrievedConfig = SystemConfiguration::find($config->id);

        // Assert
        $this->assertNull($retrievedConfig->options);
    }

    /** @test */
    public function it_has_default_values()
    {
        // Arrange & Act
        $config = SystemConfiguration::create([
            'category' => 'default_test',
            'key' => 'default_key',
            'value' => 'test_value',
            'label' => 'Test Label',
        ]);

        // Assert
        $this->assertTrue($config->is_active); // Should default to true
        $this->assertEquals(0, $config->sort_order); // Should default to 0
        $this->assertEquals('text', $config->type); // Should default to 'text'
    }

    /** @test */
    public function it_can_be_soft_deleted()
    {
        // Arrange
        $config = SystemConfiguration::factory()->create();

        // Act
        $config->delete();

        // Assert
        $this->assertSoftDeleted($config);
        $this->assertNotNull($config->fresh()->deleted_at);
    }

    /** @test */
    public function it_excludes_soft_deleted_from_queries()
    {
        // Arrange
        $activeConfig = SystemConfiguration::factory()->create(['key' => 'active']);
        $deletedConfig = SystemConfiguration::factory()->create(['key' => 'deleted']);
        $deletedConfig->delete();

        // Act
        $results = SystemConfiguration::all();

        // Assert
        $this->assertCount(1, $results);
        $this->assertEquals('active', $results->first()->key);
    }
}
