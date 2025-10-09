<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Courier;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CourierAdminController extends Controller
{
    public function index()
    {
        $couriers = Courier::with('user')->paginate(20);
        return view('admin.couriers.index', compact('couriers'));
    }

    public function create()
    {
        return view('admin.couriers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'=>'required|string|max:191',
            'email'=>'required|email|unique:users,email',
            'phone'=>'nullable|string|max:30',
            'vehicle_type'=>'nullable|string',
            'vehicle_number'=>'nullable|string',
            'commission_rate'=>'nullable|numeric|min:0',
            'password'=>'required|string|min:6|confirmed'
        ]);

        $user = User::create([
            'name'=>$data['name'],
            'email'=>$data['email'],
            'phone'=>$data['phone'] ?? null,
            'role'=>'courier',
            'password'=>Hash::make($data['password']),
        ]);

        $courier = Courier::create([
            'user_id'=>$user->id,
            'vehicle_type'=>$data['vehicle_type'] ?? null,
            'vehicle_number'=>$data['vehicle_number'] ?? null,
            'commission_rate'=>$data['commission_rate'] ?? 0,
            'status'=>'available',
        ]);

        return redirect()->route('admin.couriers.index')->with('success','Courier created.');
    }

    public function edit(Courier $courier)
    {
        $courier->load('user');
        return view('admin.couriers.edit', compact('courier'));
    }

    public function update(Request $request, Courier $courier)
    {
        $data = $request->validate([
            'name'=>'required|string|max:191',
            'email'=>'required|email|unique:users,email,'.$courier->user_id,
            'phone'=>'nullable|string|max:30',
            'vehicle_type'=>'nullable|string',
            'vehicle_number'=>'nullable|string',
            'commission_rate'=>'nullable|numeric|min:0',
            'password'=>'nullable|string|min:6|confirmed'
        ]);

        $courier->user->update([
            'name'=>$data['name'],
            'email'=>$data['email'],
            'phone'=>$data['phone'] ?? null,
        ]);

        $courier->update([
            'vehicle_type'=>$data['vehicle_type'] ?? $courier->vehicle_type,
            'vehicle_number'=>$data['vehicle_number'] ?? $courier->vehicle_number,
            'commission_rate'=>$data['commission_rate'] ?? $courier->commission_rate,
        ]);

        if (!empty($data['password'])) {
            $courier->user->update(['password'=>Hash::make($data['password'])]);
        }

        return back()->with('success','Courier updated.');
    }

    public function destroy(Courier $courier)
    {
        $courier->user->delete();
        $courier->delete();
        return back()->with('success','Courier removed.');
    }
}
