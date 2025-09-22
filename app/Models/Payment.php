<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = ['shipment_id','amount','method','status','meta'];
    public function shipment() { return $this->belongsTo(Shipment::class); }
}
