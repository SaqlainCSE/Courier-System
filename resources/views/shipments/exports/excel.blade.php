<table>
    <thead>
        <tr>
            <th>Tracking #</th>
            <th>Drop Name</th>
            <th>Drop Address</th>
            <th>Weight (kg)</th>
            <th>Status</th>
            <th>Price</th>
            <th>Delivery Fee</th>
            <th>Additional Charge</th>
            <th>Balance Cost</th>
            <th>Booked At</th>
        </tr>
    </thead>
    <tbody>
        @foreach($shipments as $shipment)
            <tr>
                <td>{{ $shipment->tracking_number }}</td>
                <td>{{ $shipment->drop_name }}</td>
                <td>{{ $shipment->drop_address }}</td>
                <td>{{ $shipment->weight_kg }}</td>
                <td>{{ $shipment->status }}</td>
                <td>{{ $shipment->price }}</td>
                <td>{{ $shipment->user->delivery_fee ?? 60 }}</td>
                <td>{{ $shipment->additional_charge }}</td>
                <td>{{ $shipment->balance_cost }}</td>
                <td>{{ $shipment->created_at->format('d M Y, H:i') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
