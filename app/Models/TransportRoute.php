<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransportRoute extends Model
{
    protected $fillable = [
        'origin', 'destination', 'origin_lat', 'origin_lng',
        'dest_lat', 'dest_lng', 'distance_km',
    ];

    public function options(): HasMany
    {
        return $this->hasMany(TransportOption::class, 'route_id');
    }
}
