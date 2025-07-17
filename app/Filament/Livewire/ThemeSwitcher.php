<?php

namespace App\Filament\Livewire;

use Filament\Support\Colors\Color;
use Livewire\Component;
class ThemeSwitcher extends Component
{
    public $selectedColor = 'blue';
    public $customColor = '#3b82f6';
    
    public function mount()
    {
        // The selected color will be managed by the client-side (Alpine.js).
        // Livewire's state is temporary and will be re-initialized on each request.
    }
    
    protected function getColors(): array
    {
        return [
            'zinc' => ['name' => 'Zinc', 'color' => '#71717a', 'palette' => Color::Zinc],
            'slate' => ['name' => 'Slate', 'color' => '#64748b', 'palette' => Color::Slate],
            'gray' => ['name' => 'Gray', 'color' => '#6b7280', 'palette' => Color::Gray],
            'red' => ['name' => 'Red', 'color' => '#ef4444', 'palette' => Color::Red],
            'orange' => ['name' => 'Orange', 'color' => '#f97316', 'palette' => Color::Orange],
            'amber' => ['name' => 'Amber', 'color' => '#f59e0b', 'palette' => Color::Amber],
            'yellow' => ['name' => 'Yellow', 'color' => '#eab308', 'palette' => Color::Yellow],
            'lime' => ['name' => 'Lime', 'color' => '#84cc16', 'palette' => Color::Lime],
            'green' => ['name' => 'Green', 'color' => '#22c55e', 'palette' => Color::Green],
            'emerald' => ['name' => 'Emerald', 'color' => '#10b981', 'palette' => Color::Emerald],
            'teal' => ['name' => 'Teal', 'color' => '#14b8a6', 'palette' => Color::Teal],
            'cyan' => ['name' => 'Cyan', 'color' => '#06b6d4', 'palette' => Color::Cyan],
            'sky' => ['name' => 'Sky', 'color' => '#0ea5e9', 'palette' => Color::Sky],
            'blue' => ['name' => 'Blue', 'color' => '#3b82f6', 'palette' => Color::Blue],
            'indigo' => ['name' => 'Indigo', 'color' => '#6366f1', 'palette' => Color::Indigo],
            'violet' => ['name' => 'Violet', 'color' => '#8b5cf6', 'palette' => Color::Violet],
            'purple' => ['name' => 'Purple', 'color' => '#a855f7', 'palette' => Color::Purple],
            'fuchsia' => ['name' => 'Fuchsia', 'color' => '#d946ef', 'palette' => Color::Fuchsia],
            'pink' => ['name' => 'Pink', 'color' => '#ec4899', 'palette' => Color::Pink],
            'rose' => ['name' => 'Rose', 'color' => '#f43f5e', 'palette' => Color::Rose],
        ];
    }



    public function render()
    {
        return view('filament.livewire.theme-switcher', [
            'colors' => $this->getColors(),
            'selectedColor' => $this->selectedColor
        ]);
    }
}
