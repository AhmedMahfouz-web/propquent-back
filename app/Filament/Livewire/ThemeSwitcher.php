<?php

namespace App\Filament\Livewire;

use Filament\Support\Colors\Color;
use Livewire\Component;

class ThemeSwitcher extends Component
{
    protected function getColors(): array
    {
        return Color::all();
    }

    public function changeColor(string $color): void
    {
        session(['theme_color' => $color]);
        $palette = $this->getColors()[$color] ?? Color::Zinc;
        $this->dispatch('update-theme-color', ['palette' => $palette]);
    }

    public function render()
    {
        return view('filament.livewire.theme-switcher');
    }
}
