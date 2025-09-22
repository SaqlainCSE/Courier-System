<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Courier;
use App\Models\User;

class CouriersSeeder extends Seeder {
    public function run() {
        $user = User::where('role','courier')->first();
        if ($user) {
            Courier::create([
                'user_id' => $user->id,
                'vehicle_type' => 'Motorbike',
                'vehicle_number' => 'BD-12-1234',
                'status' => 'available'
            ]);
        }
    }
}
