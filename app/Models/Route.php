<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Route extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'geojson',
        'scheduled_date',
        'start_time',
        'estimated_end_time',
        'status',
        'vehicle_id',
        'driver_id',
    ];

    protected $casts = [
        'geojson' => 'json',
        'scheduled_date' => 'date',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function gpsLocations(): HasMany
    {
        return $this->hasMany(GpsLocation::class, 'route_id');
    }

    public function scopeActivas($query)
    {
        return $query->where('status', '!=', 'cancelada');
    }
}
?>