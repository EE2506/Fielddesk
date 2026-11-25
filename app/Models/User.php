<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    protected $attributes = [
        'role' => 'agent',
        'policy_notice_days' => 7,
        'accepts_introductions' => false,
    ];

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'policy_notice_days' => 'integer',
            'accepts_introductions' => 'boolean',
        ];
    }

    public function homeRoute(): string
    {
        return $this->role === 'client' ? 'client.portal' : 'workspace.today';
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }
}
