<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceProvider extends Model
{
    protected $fillable = [
        'category_id', 'name', 'phone', 'description', 'city', 'address',
        'latitude', 'longitude', 'avg_rating', 'total_reviews',
        'price_min', 'price_max', 'image', 'verified', 'available',
    ];

    protected function casts(): array
    {
        return [
            'avg_rating' => 'decimal:2',
            'price_min' => 'decimal:2',
            'price_max' => 'decimal:2',
            'verified' => 'boolean',
            'available' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProviderReview::class, 'provider_id');
    }
}
