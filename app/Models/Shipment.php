<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tracking_number',
        'user_id',
        'courier_id',
        'from_branch_id',
        'pickup_name',
        'pickup_phone',
        'pickup_address',
        'pickup_lat',
        'pickup_lng',
        'drop_name',
        'drop_phone',
        'drop_address',
        'drop_lat',
        'drop_lng',
        'weight_kg',
        'price',
        'status',
        'estimated_delivery_at',
        'notes',
    ];

    // Relationships
    public function user() { return $this->belongsTo(User::class); }
    public function courier() { return $this->belongsTo(Courier::class); }
    public function branch() { return $this->belongsTo(Branch::class, 'from_branch_id'); }

    public function statusLogs()
    {
        return $this->hasMany(\App\Models\ShipmentStatusLog::class);
    }
}
