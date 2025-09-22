<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder {
    public function run() {
        User::create(['name'=>'Admin','email'=>'admin@example.com','phone'=>'0123456789','password'=>Hash::make('password'),'role'=>'admin']);
        User::create(['name'=>'Customer','email'=>'customer@example.com','phone'=>'01911111111','password'=>Hash::make('password'),'role'=>'customer']);
        User::create(['name'=>'Courier User','email'=>'courier@example.com','phone'=>'01922222222','password'=>Hash::make('password'),'role'=>'courier']);
    }
}
