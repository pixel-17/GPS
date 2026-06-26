<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'plate',
        'model',
        'year',
        'capacity',
        'status',
    ];

    public function routes(): HasMany
    {
        return $this->hasMany(Route::class, 'vehicle_id');
    }

    public function gpsLocations(): HasMany
    {
        return $this->hasMany(GpsLocation::class, 'vehicle_id');
    }

    public function scopeActivos($query)
    {
        return $query->where('status', 'activo');
    }
}
?>