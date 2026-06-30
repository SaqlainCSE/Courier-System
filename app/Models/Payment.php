<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = ['shipment_id', 'payment_invoice_id', 'invoice_number', 'amount', 'method', 'status', 'meta'];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function paymentInvoice()
    {
        return $this->belongsTo(PaymentInvoice::class);
    }

    protected static function booted()
    {
        static::creating(function ($payment) {
            if (!$payment->invoice_number && !$payment->payment_invoice_id) {
                $payment->invoice_number = '#INV-' . strtoupper(uniqid());
            }
        });
    }
}
