<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShipmentStatusLog extends Model
{
    use HasFactory;

    protected $fillable = ['shipment_id','status','changed_by','note'];

    public function shipment() { return $this->belongsTo(Shipment::class); }
    public function changer() { return $this->belongsTo(\App\Models\User::class, 'changed_by'); }

}
