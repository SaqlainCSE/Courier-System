<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Shipment;
use App\Models\User;

class ShipmentsSeeder extends Seeder {
    public function run() {
        $cust = User::where('role','customer')->first();
        if ($cust) {
            Shipment::create([
                'user_id' => $cust->id,
                'pickup_name'=>'John',
                'pickup_phone'=>'01700000000',
                'pickup_address'=>'House 10, Road 5, Dhaka',
                'drop_name'=>'Sara',
                'drop_phone'=>'01800000000',
                'drop_address'=>'House 25, Road 9, Dhaka',
                'weight_kg'=>1.2,
                'price'=>120.00
            ]);
        }
    }
}
