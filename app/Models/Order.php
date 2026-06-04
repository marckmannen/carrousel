<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

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
}
