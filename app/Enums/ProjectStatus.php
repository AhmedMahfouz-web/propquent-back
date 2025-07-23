<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case ON_GOING = 'on-going';
    case EXITED = 'exited';

    public function getLabel(): string
    {
        return match ($this) {
            self::ON_GOING => 'On-going',
            self::EXITED => 'Exited',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::ON_GOING => 'success',
            self::EXITED => 'gray',
        };
    }
}
