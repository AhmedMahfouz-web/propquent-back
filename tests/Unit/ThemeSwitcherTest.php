<?php

namespace Tests\Unit;

use App\Models\SystemConfiguration;
use App\Services\ConfigurationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ThemeSwitcherTest extends TestCase
{
    use RefreshDatabase;

    protected ConfigurationService $configurationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configurationService = app(ConfigurationService::class);
        Cache::flush();
    }

    /** @test */
    public function it_can_get_default_theme_setting()
    {
        // Arrange
        SystemConfiguration::factory()->create([
            'category' => 'theme_settings',
            'key' => 'default_theme',
            'value' => 'light',
            'label' => 'Default Theme',
            'is_active' => true,
        ]);

        // Act
        $theme = $this->configurationService->getValue('theme_settings', 'default_theme', 'light');

        // Assert
        $this->assertEquals('light', $theme);
    }

    /** @test */
    public function it_can_update_theme_setting()
    {
        // Arrange
        SystemConfiguration::factory()->create([
            'category' => 'theme_settings',
            'key' => 'default_theme',
            'value' => 'light',
            'label' => 'Default Theme',
            'is_active' => true,
        ]);

        // Act
        $result = $this->configurationService->updateConfiguration('theme_settings', 'default_theme', 'dark');

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('system_configurations', [
            'category' => 'theme_settings',
            'key' => 'default_theme',
            'value' => 'dark',
        ]);
    }

    /** @test */
    public function it_can_get_theme_options()
    {
        // Arrange
        SystemConfiguration::factory()->create([
            'category' => 'theme_settings',
            'key' => 'light',
            'value' => 'light',
            'label' => 'Light Theme',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        SystemConfiguration::factory()->create([
            'category' => 'theme_settings',
            'key' => 'dark',
            'value' => 'dark',
            'label' => 'Dark Theme',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        SystemConfiguration::factory()->create([
            'category' => 'theme_settings',
            'key' => 'auto',
            'value' => 'auto',
            'label' => 'Auto Theme',
            'is_active' => true,
            'sort_order' => 3,
        ]);

        // Act
        $options = $this->configurationService->getOptions('theme_settings');

        // Assert
        $this->assertIsArray($options);
        $this->assertCount(3, $options);
        $this->assertArrayHasKey('light', $options);
        $this->assertArrayHasKey('dark', $options);
        $this->assertArrayHasKey('auto', $options);
        $this->assertEquals('Light Theme', $options['light']);
        $this->assertEquals('Dark Theme', $options['dark']);
        $this->assertEquals('Auto Theme', $options['auto']);
    }

    /** @test */
    public function it_caches_theme_settings()
    {
        // Arrange
        SystemConfiguration::factory()->create([
            'category' => 'theme_settings',
            'key' => 'default_theme',
            'value' => 'light',
            'label' => 'Default Theme',
            'is_active' => true,
        ]);

        // Act - First call should hit database
        $firstCall = $this->configurationService->getValue('theme_settings', 'default_theme');

        // Delete the record from database
        SystemConfiguration::where('category', 'theme_settings')->delete();

        // Second call should return cached result
        $secondCall = $this->configurationService->getValue('theme_settings', 'default_theme');

        // Assert
        $this->assertEquals('light', $firstCall);
        $this->assertEquals('light', $secondCall); // Should still return cached result
    }

    /** @test */
    public function it_clears_theme_cache_when_updated()
    {
        // Arrange
        SystemConfiguration::factory()->create([
            'category' => 'theme_settings',
            'key' => 'default_theme',
            'value' => 'light',
            'label' => 'Default Theme',
            'is_active' => true,
        ]);

        // First call to populate cache
        $originalTheme = $this->configurationService->getValue('theme_settings', 'default_theme');
        $this->assertEquals('light', $originalTheme);

        // Act - Update theme setting
        $this->configurationService->updateConfiguration('theme_settings', 'default_theme', 'dark');

        // Assert - Cache should be cleared and new value returned
        $updatedTheme = $this->configurationService->getValue('theme_settings', 'default_theme');
        $this->assertEquals('dark', $updatedTheme);
    }

    /** @test */
    public function it_returns_default_theme_when_setting_not_found()
    {
        // Act
        $theme = $this->configurationService->getValue('theme_settings', 'non_existent_theme', 'light');

        // Assert
        $this->assertEquals('light', $theme);
    }

    /** @test */
    public function it_can_create_new_theme_setting()
    {
        // Arrange
        $data = [
            'category' => 'theme_settings',
            'key' => 'custom_theme',
            'value' => 'custom',
            'label' => 'Custom Theme',
            'description' => 'A custom theme setting',
            'type' => 'select',
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
    public function it_validates_theme_values()
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

        // Act
        $validThemes = $this->configurationService->getOptions('theme_settings');

        // Assert
        $this->assertArrayHasKey('light', $validThemes);
        $this->assertArrayHasKey('dark', $validThemes);
        $this->assertArrayNotHasKey('invalid_theme', $validThemes);
    }

    /** @test */
    public function it_handles_theme_persistence()
    {
        // Arrange
        $themeConfig = SystemConfiguration::factory()->create([
            'category' => 'user_preferences',
            'key' => 'theme_preference',
            'value' => 'light',
            'label' => 'User Theme Preference',
            'is_active' => true,
        ]);

        // Act - Update user theme preference
        $result = $this->configurationService->updateConfiguration('user_preferences', 'theme_preference', 'dark');

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('system_configurations', [
            'id' => $themeConfig->id,
            'value' => 'dark',
        ]);

        // Verify the updated value can be retrieved
        $updatedTheme = $this->configurationService->getValue('user_preferences', 'theme_preference');
        $this->assertEquals('dark', $updatedTheme);
    }

    /** @test */
    public function it_supports_auto_theme_detection()
    {
        // Arrange
        SystemConfiguration::factory()->create([
            'category' => 'theme_settings',
            'key' => 'auto_theme_enabled',
            'value' => 'true',
            'label' => 'Auto Theme Detection',
            'type' => 'boolean',
            'is_active' => true,
        ]);

        // Act
        $autoThemeEnabled = $this->configurationService->getValue('theme_settings', 'auto_theme_enabled', 'false');

        // Assert
        $this->assertEquals('true', $autoThemeEnabled);
    }
}
