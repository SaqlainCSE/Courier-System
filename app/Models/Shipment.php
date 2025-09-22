<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tracking_number','user_id','courier_id','from_branch_id',
        'pickup_name','pickup_phone','pickup_address','pickup_lat','pickup_lng',
        'drop_name','drop_phone','drop_address','drop_lat','drop_lng',
        'weight_kg','price','status','estimated_delivery_at','notes'
    ];

    protected static function booted()
    {
        static::creating(function ($shipment) {
            if (!$shipment->tracking_number) {
                do {
                    $tn = strtoupper(Str::random(10));
                } while (self::where('tracking_number', $tn)->exists());
                $shipment->tracking_number = $tn;
            }
        });
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function courier()
    {
        return $this->belongsTo(Courier::class, 'courier_id');
    }

    public function statusLogs()
    {
        return $this->hasMany(ShipmentStatusLog::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }
}
