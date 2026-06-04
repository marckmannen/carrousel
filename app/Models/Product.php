<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'uuid',
        'name',
        'shortDescription',
        'description',
        'imageUrl',
        'stock',
    ];

    protected function casts(): array
    {
        return [
            'stock' => 'integer',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (!$product->uuid) {
                $product->uuid = (string) Str::uuid();
            }
        });
    }
}
