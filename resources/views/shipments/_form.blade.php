@csrf
<div class="row g-4">
    <!-- Dropoff -->
    <div class="col-md-12">
        <div class="p-3 border rounded-3 bg-light h-100">
            <h5 class="fw-semibold mb-3 text-success"><i class="fas fa-map-pin me-2"></i>Dropoff Details</h5>

            <div class="mb-3">
                <label class="form-label fw-medium">Phone</label>
                <input type="text" name="drop_phone" class="form-control"
                       value="{{ old('drop_phone', $shipment->drop_phone ?? '') }}" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-medium">Name</label>
                <input type="text" name="drop_name" class="form-control"
                       value="{{ old('drop_name', $shipment->drop_name ?? '') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-medium">District</label>
                <select id="drop_district" class="form-select" required></select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-medium">Police Station</label>
                <select id="drop_police" class="form-select" required></select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-medium">Area</label>
                <select id="drop_area" class="form-select" required></select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-medium">Street / House / Road</label>
                <input type="text" id="drop_street" name="drop_street" class="form-control"
                       placeholder="e.g. Lane #5, Flat #A2"
                       value="{{ old('drop_street', $shipment->drop_street ?? '') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-medium">Full Address</label>
                <input type="text" name="drop_address" id="drop_address"
                       class="form-control bg-light"
                       value="{{ old('drop_address', $shipment->drop_address ?? '') }}" readonly required>
            </div>
        </div>
    </div>
</div>

<!-- Shipment Info -->
<div class="row g-3 mt-4">
    <div class="col-md-4">
        <label class="form-label fw-medium">Total Price of Product</label>
        <input type="number" id="price" name="price"
               class="form-control" step="0.1" min="0"
               value="{{ old('price', $shipment->price ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label fw-medium">Weight (kg)</label>
        <input type="number" id="weight" name="weight_kg"
               class="form-control" step="0.1" min="0.1"
               value="{{ old('weight_kg', $shipment->weight_kg ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label fw-medium">Notes (optional)</label>
        <input type="text" name="notes" class="form-control"
               value="{{ old('notes', $shipment->notes ?? '') }}">
    </div>
</div>

<!-- Live Price Display -->
<div class="mt-4">
    <div class="alert alert-info d-flex align-items-center justify-content-between">
        <span><i class="fas fa-coins me-2"></i><strong>Delivery Fee:</strong></span>
        <span id="delivery_fee" class="fs-5 fw-bold text-success">
            ৳ {{ old('cost_of_delivery_amount', $shipment->cost_of_delivery_amount ?? 60) }}
        </span>
    </div>
</div>

<!-- Hidden input to store the calculated delivery fee -->
<input type="hidden" name="cost_of_delivery_amount" id="delivery_fee_input"
       value="{{ old('cost_of_delivery_amount', $shipment->cost_of_delivery_amount ?? 60) }}">


<!-- Buttons -->
<div class="mt-3">
    <button type="submit" class="btn btn-success shadow">
    {{ $buttonText ?? 'Save Shipment' }}
    </button>
    <a href="{{ route('shipments.dashboard') }}" class="btn btn-secondary">Cancel</a>
</div>

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
    const deliveryFeeDisplay = document.getElementById('delivery_fee');
    const deliveryFeeInput = document.getElementById('delivery_fee_input');

    function calculateDeliveryFee(weight) {
        let fee = 60; // minimum price
        if(weight > 1){
            let additionalKg = Math.ceil(weight - 1) * 10;
            fee += additionalKg;
        }
        return fee;
    }

    // Initialize on page load
    deliveryFeeDisplay.textContent = `৳ ${calculateDeliveryFee(parseFloat(weightInput.value) || 0)}`;
    deliveryFeeInput.value = calculateDeliveryFee(parseFloat(weightInput.value) || 0);

    // Update live when weight changes
    weightInput.addEventListener('input', function() {
        const weight = parseFloat(this.value) || 0;
        const fee = calculateDeliveryFee(weight);
        deliveryFeeDisplay.textContent = `৳ ${fee}`;
        deliveryFeeInput.value = fee;
    });

});
</script>
@endpush
