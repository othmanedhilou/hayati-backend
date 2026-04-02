<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceCategory extends Model
{
    protected $fillable = ['name', 'name_ar', 'icon', 'slug'];

    public function providers(): HasMany
    {
        return $this->hasMany(ServiceProvider::class, 'category_id');
    }
}
