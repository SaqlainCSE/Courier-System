<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Courier extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vehicle_type',
        'vehicle_number',
        'current_lat',
        'current_lng',
        'status',
        'commission_rate',
    ];

    // relationships
    public function user() {
        return $this->belongsTo(User::class);
    }

    public function assignedShipments()
    {
        return $this->hasMany(Shipment::class, 'courier_id');
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class, 'courier_id');
    }


}
