<?php

namespace Tests\Feature\Admin;

use App\Models\Courier;
use App\Models\Shipment;
use App\Models\ShipmentStatusLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CourierAdminViewCalculationsTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private Courier $courier;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-06-11 12:00:00'));

        $this->admin = User::factory()->create(['role' => 'admin', 'phone' => '01700000001']);

        $courierUser = User::factory()->create(['role' => 'courier', 'phone' => '01700000002']);
        $this->courier = Courier::create([
            'user_id' => $courierUser->id,
            'commission_rate' => 10,
            'status' => 'available',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function createShipment(array $attributes = []): Shipment
    {
        $customer = User::factory()->create(['role' => 'customer', 'phone' => '017' . random_int(10000000, 99999999)]);

        return Shipment::create(array_merge([
            'tracking_number' => 'TRK' . uniqid(),
            'user_id' => $customer->id,
            'courier_id' => $this->courier->id,
            'drop_name' => 'Receiver',
            'drop_phone' => '01712345678',
            'drop_address' => 'Dhaka',
            'price' => 100,
            'partial_price' => 0,
            'status' => 'assigned',
        ], $attributes));
    }

    private function logAssignedToday(Shipment $shipment): void
    {
        ShipmentStatusLog::create([
            'shipment_id' => $shipment->id,
            'user_id' => $this->courier->user_id,
            'status' => 'assigned',
            'changed_by' => $this->admin->id,
        ]);
    }

    private function viewCalculations(): array
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.couriers.view', $this->courier->id));

        $response->assertOk();

        return [
            'commission' => $response->viewData('commission'),
            'todayEarnings' => $response->viewData('todayEarnings'),
            'totalDeliveredAmount' => $response->viewData('totalDeliveredAmount'),
            'totalDeliveredShipments' => $response->viewData('totalDeliveredShipments'),
            'todayAssignedTotalAmount' => $response->viewData('todayAssignedTotalAmount'),
            'todayAssignedCommission' => $response->viewData('todayAssignedCommission'),
            'todayPartialDeliveredTotal' => $response->viewData('todayPartialDeliveredTotal'),
            'todayNetAfterCommission' => $response->viewData('todayNetAfterCommission'),
            'statusSummary' => $response->viewData('statusSummary'),
            'shipments' => $response->viewData('shipments'),
        ];
    }

    public function test_total_commission_and_collected_amounts(): void
    {
        $this->createShipment(['status' => 'delivered', 'price' => 200, 'delivered_at' => now()]);
        $this->createShipment(['status' => 'partially_delivered', 'price' => 300, 'partial_price' => 120, 'delivered_at' => now()]);
        $this->createShipment(['status' => 'cancelled', 'price' => 50, 'delivered_at' => now()]);
        $this->createShipment(['status' => 'in_transit', 'price' => 80]);

        $data = $this->viewCalculations();

        $this->assertSame(30.0, (float) $data['commission']);
        $this->assertSame(320.0, (float) $data['totalDeliveredAmount']);
        $this->assertSame(2, $data['totalDeliveredShipments']);
        $this->assertSame(1, $data['statusSummary']['delivered']);
        $this->assertSame(1, $data['statusSummary']['partially_delivered']);
        $this->assertSame(1, $data['statusSummary']['cancelled']);
        $this->assertSame(1, $data['statusSummary']['in_transit']);
    }

    public function test_today_earnings_use_delivered_at_not_updated_at(): void
    {
        $deliveredToday = $this->createShipment([
            'status' => 'delivered',
            'price' => 100,
            'delivered_at' => now(),
        ]);
        $deliveredToday->forceFill(['updated_at' => now()])->save();

        $deliveredYesterday = $this->createShipment([
            'status' => 'delivered',
            'price' => 100,
            'delivered_at' => now()->subDay(),
        ]);
        $deliveredYesterday->forceFill(['updated_at' => now()])->save();

        $data = $this->viewCalculations();

        $this->assertSame(10.0, (float) $data['todayEarnings']);
    }

    public function test_today_assigned_batch_calculations(): void
    {
        $delivered = $this->createShipment([
            'status' => 'delivered',
            'price' => 200,
            'delivered_at' => now(),
        ]);
        $partial = $this->createShipment([
            'status' => 'partially_delivered',
            'price' => 300,
            'partial_price' => 150,
            'delivered_at' => now(),
        ]);
        $cancelled = $this->createShipment([
            'status' => 'cancelled',
            'price' => 100,
            'delivered_at' => now(),
        ]);
        $pending = $this->createShipment([
            'status' => 'assigned',
            'price' => 500,
        ]);

        foreach ([$delivered, $partial, $cancelled, $pending] as $shipment) {
            $this->logAssignedToday($shipment);
        }

        $data = $this->viewCalculations();

        $this->assertSame(1100.0, (float) $data['todayAssignedTotalAmount']);
        $this->assertSame(30.0, (float) $data['todayAssignedCommission']);
        $this->assertSame(150.0, (float) $data['todayPartialDeliveredTotal']);
        $this->assertSame(320.0, (float) $data['todayNetAfterCommission']);
    }

    public function test_date_filter_uses_created_at_not_updated_at(): void
    {
        $inRange = $this->createShipment([
            'status' => 'delivered',
            'delivered_at' => Carbon::parse('2026-06-10 10:00:00'),
        ]);
        $inRange->forceFill([
            'created_at' => Carbon::parse('2026-06-05 10:00:00'),
            'updated_at' => Carbon::parse('2026-06-10 10:00:00'),
        ])->save();

        $outOfRange = $this->createShipment([
            'status' => 'delivered',
            'delivered_at' => Carbon::parse('2026-06-10 10:00:00'),
        ]);
        $outOfRange->forceFill([
            'created_at' => Carbon::parse('2026-06-01 10:00:00'),
            'updated_at' => Carbon::parse('2026-06-10 10:00:00'),
        ])->save();

        $response = $this->actingAs($this->admin)->get(route('admin.couriers.view', [
            'courier' => $this->courier->id,
            'from' => '2026-06-04',
            'to' => '2026-06-06',
        ]));

        $response->assertOk();
        $shipments = $response->viewData('shipments');

        $this->assertCount(1, $shipments);
        $this->assertSame($inRange->id, $shipments->first()->id);
        $this->assertFalse($shipments->contains('id', $outOfRange->id));
    }
}
