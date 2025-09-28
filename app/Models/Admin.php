<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Admin extends Authenticatable
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password_hash',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    public function getAuthPassword()
    {
        return $this->password_hash;
    }
}
