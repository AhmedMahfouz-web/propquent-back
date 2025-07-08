<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Filament Default Auth Guard
    |--------------------------------------------------------------------------
    |
    | This guard will be used by Filament to authenticate users. We have
    | created a separate "admins" guard that authenticates against the
    | `admins` table / Admin model.
    |
    */

    'auth_guard' => 'admins',

    'dark_mode' => [
        'enabled' => true,
        'default' => false,
    ],

];
