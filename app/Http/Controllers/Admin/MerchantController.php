<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;


class MerchantController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->where('role', 'customer');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('name','like',"%{$q}%")
                    ->orWhere('business_name','like',"%{$q}%")
                    ->orWhere('email','like',"%{$q}%")
                    ->orWhere('phone','like',"%{$q}%");
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at','>=',$request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at','<=',$request->end_date);
        }

        $merchants = $query->latest()->paginate(20)->withQueryString();

        $summary = [
            'total' => User::where('role','customer')->count(),
            'new_today' => User::where('role','customer')->whereDate('created_at',now()->toDateString())->count(),
            'new_this_week' => User::where('role','customer')->whereBetween('created_at',[now()->startOfWeek(), now()->endOfWeek()])->count(),
            'new_this_month' => User::where('role','customer')->whereBetween('created_at',[now()->startOfMonth(), now()->endOfMonth()])->count(),
        ];

        return view('admin.merchants.index', compact('merchants','summary'));
    }

    public function create()
    {
        return view('admin.merchants.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'business_name' => 'nullable|string|max:191',
            'business_address' => 'nullable|string|max:500',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:50',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'business_name' => $data['business_name'] ?? null,
            'business_address' => $data['business_address'] ?? null,
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => isset($data['password']) ? Hash::make($data['password']) : Hash::make(Str::random(10)),
            'role' => 'customer',
        ]);

        return redirect()->route('admin.merchants.index')->with('success','Merchant created.');
    }

    public function show(User $merchant)
    {
        if ($merchant->role !== 'customer') abort(404);

        $shipments = $merchant->shipments()->latest()->paginate(20);

        $summary = [
            'total_shipments' => $merchant->shipments()->count(),
            'delivered' => $merchant->shipments()->where('status','delivered')->count(),
            'in_transit' => $merchant->shipments()->where('status','in_transit')->count(),
            'cancelled' => $merchant->shipments()->where('status','cancelled')->count(),
            'partially_delivered' => $merchant->shipments()->where('status','partially_delivered')->count(),
            'hold' => $merchant->shipments()->where('status','hold')->count(),
            'assigned' => $merchant->shipments()->where('status','assigned')->count(),
            'picked' => $merchant->shipments()->where('status','picked')->count(),
            'pending' => $merchant->shipments()->where('status','pending')->count(),
            'balance' => $merchant->shipments()->whereIn('status',['delivered','partially_delivered'])->sum('balance_cost'),
        ];

        return view('admin.merchants.show', compact('merchant','shipments','summary'));
    }

    public function edit(User $merchant)
    {
        if ($merchant->role !== 'customer') abort(404);
        return view('admin.merchants.edit', compact('merchant'));
    }

    public function update(Request $request, User $merchant)
    {
        if ($merchant->role !== 'customer') abort(404);

        $data = $request->validate([
            'name' => 'required|string|max:191',
            'business_name' => 'nullable|string|max:191',
            'business_address' => 'nullable|string|max:500',
            'email' => ['required','email', Rule::unique('users','email')->ignore($merchant->id)],
            'phone' => 'nullable|string|max:50',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $merchant->name = $data['name'];
        $merchant->business_name = $data['business_name'] ?? null;
        $merchant->business_address = $data['business_address'] ?? null;
        $merchant->email = $data['email'];
        $merchant->phone = $data['phone'] ?? null;

        if (!empty($data['password'])) {
            $merchant->password = Hash::make($data['password']);
        }

        $merchant->save();

        return redirect()->route('admin.merchants.index')->with('success','Merchant updated.');
    }

    public function destroy(User $merchant)
    {
        if ($merchant->role !== 'customer') abort(404);
            $merchant->delete();
        return back()->with('success','Merchant deleted.');
    }
}
