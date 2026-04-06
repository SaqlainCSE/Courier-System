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
        'cost_of_delivery_amount',
        'additional_charge',
        'balance_cost',
        'status',
        'estimated_delivery_at',
        'notes',
        'delivered_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
    ];

    // Relationships
    public function user() { return $this->belongsTo(User::class); }
    public function courier() { return $this->belongsTo(Courier::class); }
    public function branch() { return $this->belongsTo(Branch::class, 'from_branch_id'); }

    public function statusLogs()
    {
        return $this->hasMany(\App\Models\ShipmentStatusLog::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected static function booted()
    {
        static::updating(function ($shipment) {
            if (
                in_array($shipment->status, ['delivered', 'partially_delivered', 'cancelled']) &&
                is_null($shipment->delivered_at)
            ) {
                $shipment->delivered_at = now();
            }
        });
    }

    public function getIsPaidAttribute(): bool
    {
        return in_array($this->status, ['delivered', 'partially_delivered'])
            && $this->balance_cost <= 0;
    }

}
