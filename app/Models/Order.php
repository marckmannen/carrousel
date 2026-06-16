<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function ($order) {
            //
        });

        static::created(function ($order) {
            //
            if (empty($order->order_id)) {
                $order->order_id = (string) $order->id;
                $order->saveQuietly();
            }
        });
    }

    protected $fillable = [
        'user_id',
        'pharmacy_id',
        'order_id',
        'product_id',
        'product_name',
        'amount',
        'status',
        'pincode',
        'birthdate',
        'birthday_id',
        'pincode_id',
        'api_response',
    ];

    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
            'api_response' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function birthday(): BelongsTo
    {
        return $this->belongsTo(Birthday::class);
    }

    public function pincodeRecord(): BelongsTo
    {
        return $this->belongsTo(Pincode::class, 'pincode_id');
    }

    /**
     * Release the associated pincode so it can be reused.
     * Called when an order is cancelled or picked up.
     */
    public function releasePincode(): void
    {
        if ($this->pincodeRecord) {
            $this->pincodeRecord->release();
        }
    }
}
