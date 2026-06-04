<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'birthdate'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Valid role values for this application.
     */
    public const ROLE_USER = 'user';
    public const ROLE_ADMIN = 'admin';

    /**
     * Get the orders for the user.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Set the role, ensuring only valid values are stored.
     */
    public function setRoleAttribute(string $value): void
    {
        if (!in_array($value, [self::ROLE_USER, self::ROLE_ADMIN], true)) {
            $this->attributes['role'] = self::ROLE_USER;
        } else {
            $this->attributes['role'] = $value;
        }
    }

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
            'birthdate' => 'date',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }
}
