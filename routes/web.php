<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\CourierController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\Admin\ShipmentAdminController;
use App\Http\Controllers\Admin\CourierAdminController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ReportController;

Route::get('/', function () {
    if(\Illuminate\Support\Facades\Auth::check()){
        $role = \Illuminate\Support\Facades\Auth::user()->role;
        if($role === 'admin') return redirect()->route('admin.dashboard');
        if($role === 'courier') return redirect()->route('courier.dashboard');
        return redirect()->route('shipments.dashboard');
    }
    return view('welcome');
});


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/track', function(){ return view('tracking.form'); })->name('tracking.form');
Route::post('/track', [TrackingController::class,'search'])->name('tracking.search');
Route::get('/track/{tracking}', [TrackingController::class,'show'])->name('tracking.show');

Route::get('/get-dropoff-details', [ShipmentController::class, 'getDropoffDetails']);
Route::get('/shipments/{shipment}/print', [ShipmentController::class, 'print'])->name('shipments.print');
Route::get('/shipments/print/all', [ShipmentController::class, 'printAll'])->name('shipments.print.all');

Route::middleware('auth')->group(function() {

    // customer shipments
    Route::prefix('customer')->middleware('role:customer')->group(function() {
        Route::get('/shipments/dashboard', [ShipmentController::class,'dashboard'])->name('shipments.dashboard');
        Route::get('/shipments/create', [ShipmentController::class,'create'])->name('shipments.create');
        Route::post('/shipments', [ShipmentController::class,'store'])->name('shipments.store');
        Route::get('/shipments/{shipment}', [ShipmentController::class,'show'])->name('shipments.show');
        Route::post('/shipments/{shipment}/cancel', [ShipmentController::class,'cancel'])->name('shipments.cancel');
        Route::get('/shipments/{shipment}/edit', [ShipmentController::class, 'edit'])->name('shipments.edit');
        Route::put('/shipments/{shipment}', [ShipmentController::class, 'update'])->name('shipments.update');
    });

    // courier
    Route::prefix('courier')->middleware('role:courier')->group(function(){
        Route::get('/dashboard', [CourierController::class,'dashboard'])->name('courier.dashboard');
        Route::post('/shipments/{shipment}/status', [CourierController::class,'updateStatus'])->name('courier.shipments.updateStatus');
        Route::post('/location', [CourierController::class,'updateLocation'])->name('courier.location.update');
        Route::get('/shipments/history', [CourierController::class,'history'])->name('courier.shipments.history');
        Route::get('/shipments/{shipment}', [CourierController::class,'show'])->name('courier.shipments.show');
    });

    // admin
    Route::prefix('admin')->middleware('role:admin')->group(function(){

        Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');

        Route::get('/shipments', [ShipmentAdminController::class,'index'])->name('admin.shipments.index');
        Route::get('/shipments/{shipment}', [ShipmentAdminController::class,'show'])->name('admin.shipments.show');
        Route::post('/shipments/{shipment}/assign', [ShipmentAdminController::class,'assignCourier'])->name('admin.shipments.assign');
        Route::post('/shipments/{shipment}/status', [ShipmentAdminController::class,'updateStatus'])->name('admin.shipments.updateStatus');

        // Couriers (management)
        Route::resource('couriers', CourierAdminController::class, ['as' => 'admin']);

        // Reports / Export
        Route::get('/reports', [ReportController::class, 'index'])->name('admin.reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('admin.reports.export');

        Route::get('/shipments/print/all', [ShipmentAdminController::class, 'printAll'])->name('admin.shipments.print.all');

    });
});

require __DIR__.'/auth.php';
