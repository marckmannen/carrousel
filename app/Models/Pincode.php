<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pincode extends Model
{
    protected $fillable = ['code', 'available'];

    protected function casts(): array
    {
        return [
            'available' => 'boolean',
        ];
    }

    /**
     * Get an available pincode, creating one if none exist.
     */
    public static function getAvailable(): self
    {
        $pincode = static::where('available', true)->inRandomOrder()->first();

        if (!$pincode) {
            // Generate a unique pincode that doesn't exist yet
            do {
                $code = sprintf('%04d', random_int(1000, 9999));
            } while (static::where('code', $code)->exists());

            $pincode = static::create(['code' => $code, 'available' => true]);
        }

        return $pincode;
    }

    /**
     * Claim this pincode (mark as unavailable).
     */
    public function claim(): bool
    {
        return $this->update(['available' => false]);
    }

    /**
     * Release this pincode (mark as available again).
     */
    public function release(): bool
    {
        return $this->update(['available' => true]);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'pincode_id');
    }
}
