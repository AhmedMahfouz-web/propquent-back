<?php

namespace App\Http\Livewire;

use Filament\Support\Colors\Color;
use Livewire\Component;

class ThemeSwitcherPanel extends Component
{
    protected function getColors(): array
    {
        return [
            'zinc' => Color::Zinc,
            'slate' => Color::Slate,
            'stone' => Color::Stone,
            'gray' => Color::Gray,
            'neutral' => Color::Neutral,
            'red' => Color::Red,
            'orange' => Color::Orange,
            'amber' => Color::Amber,
            'yellow' => Color::Yellow,
            'lime' => Color::Lime,
            'green' => Color::Green,
            'emerald' => Color::Emerald,
            'teal' => Color::Teal,
            'cyan' => Color::Cyan,
            'sky' => Color::Sky,
            'blue' => Color::Blue,
            'indigo' => Color::Indigo,
            'violet' => Color::Violet,
            'purple' => Color::Purple,
            'fuchsia' => Color::Fuchsia,
            'pink' => Color::Pink,
            'rose' => Color::Rose,
        ];
    }

    public function changeColor(string $color): void
    {
        session(['theme_color' => $color]);

        $palette = $this->getColors()[$color] ?? Color::Zinc;

        $this->dispatchBrowserEvent('update-theme-color', ['palette' => $palette]);
    }

    public function render()
    {
        return view('livewire.theme-switcher-panel');
    }
}
