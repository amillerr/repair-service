<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    public const ROLE_DISPATCHER = 'dispatcher';
    public const ROLE_MASTER = 'master';

    protected $fillable = [
        'name',
        'role',
    ];

    public function isMaster(): bool
    {
        return $this->role === self::ROLE_MASTER;
    }

    public function isDispatcher(): bool
    {
        return $this->role === self::ROLE_DISPATCHER;
    }
}
