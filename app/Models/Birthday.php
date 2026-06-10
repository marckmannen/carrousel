<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Birthday extends Model
{
    protected $table = 'birthdates';

    protected $fillable = ['date'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    /**
     * Get or create a birthday record for a given date.
     */
    public static function getOrCreate(string $date): self
    {
        return static::firstOrCreate(['date' => $date]);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'birthday_id');
    }
}
