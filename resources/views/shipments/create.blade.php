@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="fw-bold mb-4">📦 Create Shipment</h2>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('shipments.store') }}" method="POST">
                @csrf

                <div class="row g-3">
                    <!-- Pickup -->
                    <div class="col-md-6">
                        <h5 class="fw-semibold mb-3">Pickup Details</h5>

                        <div class="mb-3">
                            <label>Name</label>
                            <input type="text" name="pickup_name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>Phone</label>
                            <input type="text" name="pickup_phone" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>District</label>
                            <select id="pickup_district" class="form-select" required></select>
                        </div>

                        <div class="mb-3">
                            <label>Police Station</label>
                            <select id="pickup_police" class="form-select" required></select>
                        </div>

                        <div class="mb-3">
                            <label>Area</label>
                            <select id="pickup_area" class="form-select" required></select>
                        </div>

                        <div class="mb-3">
                            <label>Street / House / Road</label>
                            <input type="text" id="pickup_street" class="form-control" placeholder="Street / House / Road" required>
                        </div>

                        <div class="mb-3">
                            <label>Full Address (Breadcrumb)</label>
                            <input type="text" name="pickup_address" id="pickup_address" class="form-control" readonly required>
                        </div>
                    </div>

                    <!-- Dropoff -->
                    <div class="col-md-6">
                        <h5 class="fw-semibold mb-3">Dropoff Details</h5>

                        <div class="mb-3">
                            <label>Name</label>
                            <input type="text" name="drop_name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>Phone</label>
                            <input type="text" name="drop_phone" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>District</label>
                            <select id="drop_district" class="form-select" required></select>
                        </div>

                        <div class="mb-3">
                            <label>Police Station</label>
                            <select id="drop_police" class="form-select" required></select>
                        </div>

                        <div class="mb-3">
                            <label>Area</label>
                            <select id="drop_area" class="form-select" required></select>
                        </div>

                        <div class="mb-3">
                            <label>Street / House / Road</label>
                            <input type="text" id="drop_street" class="form-control" placeholder="Street / House / Road" required>
                        </div>

                        <div class="mb-3">
                            <label>Full Address (Breadcrumb)</label>
                            <input type="text" name="drop_address" id="drop_address" class="form-control" readonly required>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-md-4">
                        <label>Weight (kg)</label>
                        <input type="number" id="weight" name="weight_kg" class="form-control" step="0.1" min="0.1" required>
                    </div>
                    <div class="col-md-8">
                        <label>Notes (optional)</label>
                        <input type="text" name="notes" class="form-control">
                    </div>
                </div>

                <!-- Live Price Display -->
                <div class="mt-3">
                    <h5>Delivery Fee: <span id="price" class="text-danger fw-bold">৳ 60</span></h5>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-success shadow">🚀 Book Shipment</button>
                    <a href="{{ route('shipments.dashboard') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Locations data (static, no DB)
    const locations = {
        "Dhaka": {
            "Dhanmondi": ["Dhanmondi 1", "Dhanmondi 2", "Dhanmondi 3", "Dhanmondi 4", "Dhanmondi 5", "Dhanmondi 6", "Dhanmondi 7", "Dhanmondi 8", "Dhanmondi 9", "Dhanmondi 10"],
            "Gulshan": ["Gulshan 1", "Gulshan 2"],
            "Banani": ["Block A", "Block B", "Block C", "Block D"],
            "Motijheel": ["Motijheel North", "Motijheel South"],
            "Mirpur": ["Section 1", "Section 2", "Section 6", "Section 10", "Pallabi", "Kafrul", "Tongi"],
            "Uttara": ["Sector 1", "Sector 2", "Sector 3", "Sector 4", "Sector 5", "Sector 6", "Sector 7", "Sector 8", "Sector 9", "Sector 10", "Sector 11", "Sector 12", "Sector 13", "Sector 14", "Sector 15", "Sector 16", "Sector 17", "Sector 18", "Sector 19", "Sector 20", "Sector 21", "Sector 22", "Sector 23", "Sector 24"],
            "Mohammadpur": ["Block A", "Block B", "Block C", "Block D", "Shyamoli"],
            "Tejgaon": ["Tejgaon I/A", "Tejgaon I/B", "Tejgaon II/A", "Tejgaon II/B", "Tejgaon Industrial Area"],
            "Khilgaon": [
                                    "Taltola",
                                    "Sipahibag",
                                    "Chowdhury Para)",
                                    "Meradia",
                                    "Goran",
                                    "Tilpapara",
                                    "Nayabasti",
                                    "East Khilgaon",
                                    "West Khilgaon","Bashabo", "Mugda"],
            "Rampura": ["Rampura North", "Rampura South", "Rampura DOHS"],
            "Shahbagh": ["Shahbagh Main", "Shahbagh Extension", "Bangla Motor"],
            "Paltan": ["Paltan Main", "Paltan South", "Paltan North"],
            "Mohakhali": ["Mohakhali DOHS", "Mohakhali Main", "Mohakhali Residential Area", "Tejgaon Industrial Area"],
            "Badda": ["Badda East", "Badda West", "Middle Badda", "Merul Badda"],
            "Uttarkhan": ["Sector 1", "Sector 2", "Sector 3", "Sector 4", "Sector 5"],
            "Jatrabari": ["North", "South", "Central"],
            "Kamrangirchar": ["Zone 1", "Zone 2", "Zone 3", "Zone 4"],
            "Shyamoli": ["Block A", "Block B", "Block C"],
            "Doyagonj": ["Area 1", "Area 2", "Area 3"],
            "Mohammadpur Thana": ["Mohammadpur Main", "Shyamoli", "Green Road"],
            "New Market": ["Kakrail", "Green Road", "Old Elephant Road", "New Elephant Road"],
            "Mirpur DOHS": ["Block A", "Block B", "Block C"],
            "Tejgaon Industrial": ["Tejgaon I/A", "Tejgaon I/B", "Tejgaon II/A", "Tejgaon II/B"],
            "Rampura DOHS": ["Sector 1", "Sector 2", "Sector 3"]
        }
    };


    // Generic helpers
    function populateSelect(select, options){
        select.innerHTML = '<option value="">Select</option>';
        Object.keys(options).forEach(key => select.innerHTML += `<option value="${key}">${key}</option>`);
    }

    function populateChild(parent, child, obj){
        child.innerHTML = '<option value="">Select</option>';
        if(parent.value && obj[parent.value]){
            Object.keys(obj[parent.value]).forEach(k => child.innerHTML += `<option value="${k}">${k}</option>`);
        }
    }

    function populateGrandchild(parent, child, grandchild, obj){
        grandchild.innerHTML = '<option value="">Select</option>';
        if(parent.value && child.value && obj[parent.value][child.value]){
            obj[parent.value][child.value].forEach(v => grandchild.innerHTML += `<option value="${v}">${v}</option>`);
        }
    }

    function updateBreadcrumb(district, police, area, street, target){
        const parts = [];
        if(district.value) parts.push(district.value);
        if(police.value) parts.push(police.value);
        if(area.value) parts.push(area.value);
        if(street.value) parts.push(street.value);
        target.value = parts.join(' > ');
    }

    // Pickup
    const pickupDistrict = document.getElementById('pickup_district');
    const pickupPolice = document.getElementById('pickup_police');
    const pickupArea = document.getElementById('pickup_area');
    const pickupStreet = document.getElementById('pickup_street');
    const pickupAddress = document.getElementById('pickup_address');

    populateSelect(pickupDistrict, locations);
    pickupDistrict.addEventListener('change', ()=> populateChild(pickupDistrict, pickupPolice, locations));
    pickupPolice.addEventListener('change', ()=> populateGrandchild(pickupDistrict, pickupPolice, pickupArea, locations));
    [pickupDistrict, pickupPolice, pickupArea, pickupStreet].forEach(el=>{
        el.addEventListener('change', ()=> updateBreadcrumb(pickupDistrict, pickupPolice, pickupArea, pickupStreet, pickupAddress));
    });

    // Dropoff
    const dropDistrict = document.getElementById('drop_district');
    const dropPolice = document.getElementById('drop_police');
    const dropArea = document.getElementById('drop_area');
    const dropStreet = document.getElementById('drop_street');
    const dropAddress = document.getElementById('drop_address');

    populateSelect(dropDistrict, locations);
    dropDistrict.addEventListener('change', ()=> populateChild(dropDistrict, dropPolice, locations));
    dropPolice.addEventListener('change', ()=> populateGrandchild(dropDistrict, dropPolice, dropArea, locations));
    [dropDistrict, dropPolice, dropArea, dropStreet].forEach(el=>{
        el.addEventListener('change', ()=> updateBreadcrumb(dropDistrict, dropPolice, dropArea, dropStreet, dropAddress));
    });

    // Live Price Calculation
    const weightInput = document.getElementById('weight');
    const priceDisplay = document.getElementById('price');

    function calculatePrice(weight) {
        let price = 60; // minimum price
        if(weight > 1){
            let additionalKg = Math.ceil(weight - 1);
            price += additionalKg * 10;
        }
        return price;
    }

    weightInput.addEventListener('input', function() {
        const weight = parseFloat(this.value) || 0;
        const price = calculatePrice(weight);
        priceDisplay.textContent = `৳ ${price}`;
    });
});
</script>
@endpush
