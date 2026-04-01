@csrf
<div class="row g-4">
    <!-- Dropoff -->
    <div class="col-md-12">
        <div class="p-3 border rounded-3 bg-light h-100">
            <h5 class="fw-semibold mb-3 text-success"><i class="fas fa-map-pin me-2"></i>Dropoff Details</h5>

            <div class="mb-3">
                <label class="form-label fw-medium">Phone</label>
                <input type="text" name="drop_phone" id="drop_phone" class="form-control"
                    value="{{ old('drop_phone', $shipment->drop_phone ?? '') }}" required>

                <small id="phone_error" class="text-danger d-none">
                    Invalid Bangladesh phone number
                </small>
            </div>

            <div class="mb-3">
                <label class="form-label fw-medium">Name</label>
                <input type="text" name="drop_name" class="form-control"
                       value="{{ old('drop_name', $shipment->drop_name ?? '') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-medium">District</label>
                <select id="drop_district" class="form-select"></select>
            </div>

            {{--  <div class="mb-3">
                <label class="form-label fw-medium">Police Station</label>
                <select id="drop_police" class="form-select"></select>
            </div>  --}}

            <div class="mb-3">
                <label class="form-label fw-medium">Area</label>
                <select id="drop_area" class="form-select"></select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-medium">Street / House / Road</label>
                <input type="text" id="drop_street" name="drop_street" class="form-control"
                       placeholder="e.g. Lane #5, Flat #A2"
                       value="{{ old('drop_street', $shipment->drop_street ?? '') }}">
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

        <small id="price_error" class="text-danger d-none">
            Price must be 0 or greater
        </small>
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
            "Dhaka": [
                "Dhanmondi",
                "Gulshan",
                "Banani",
                "Motijheel",
                "Mirpur",
                "Uttara",
                "Mohammadpur",
                "Tejgaon",
                "Khilgaon",
                "Rampura",
                "Shahbagh",
                "Paltan",
                "Mohakhali",
                "Badda",
                "Uttarkhan",
                "Jatrabari",
                "Kamrangirchar",
                "Shyamoli",
                "Doyagonj",
                "New Market"
            ]
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

        function populateArea(parent, child, obj){
            child.innerHTML = '<option value="">Select Area</option>';

            if(parent.value && obj[parent.value]){
                obj[parent.value].forEach(area => {
                    child.innerHTML += `<option value="${area}">${area}</option>`;
                });
            }
        }

        function updateBreadcrumb(district, area, street, target){
            const parts = [];
            if(district.value) parts.push(district.value);
            // if(police.value) parts.push(police.value);
            if(area.value) parts.push(area.value);
            if(street.value) parts.push(street.value);
            target.value = parts.join(' > ');
        }

        // Dropoff
        const dropDistrict = document.getElementById('drop_district');
        // const dropPolice = document.getElementById('drop_police');
        const dropArea = document.getElementById('drop_area');
        const dropStreet = document.getElementById('drop_street');
        const dropAddress = document.getElementById('drop_address');

        populateSelect(dropDistrict, locations);

        dropDistrict.addEventListener('change', () => {
            populateArea(dropDistrict, dropArea, locations);
        });

        // dropPolice.addEventListener('change', ()=> populateGrandchild(dropDistrict, dropPolice, dropArea, locations));
        [dropDistrict, /* dropPolice, */ dropArea, dropStreet].forEach(el=>{
            el.addEventListener('change', ()=> updateBreadcrumb(dropDistrict, /* dropPolice, */ dropArea, dropStreet, dropAddress));
        });

        // Live Price Calculation
        const weightInput = document.getElementById('weight');
        const deliveryFeeDisplay = document.getElementById('delivery_fee');
        const deliveryFeeInput = document.getElementById('delivery_fee_input');

        let baseFee = {{ auth()->user()->delivery_fee ?? 60 }};

        function calculateDeliveryFee(weight) {

            let fee = baseFee;
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

<script>
    $(document).on("input", "#drop_phone", function () {
        let phone = $(this).val();
        let regex = /^01[3-9][0-9]{8}$/;

        if (regex.test(phone)) {
            $("#phone_error").addClass("d-none");
            $(this).removeClass("is-invalid").addClass("is-valid");
        } else {
            $("#phone_error").removeClass("d-none");
            $(this).removeClass("is-valid").addClass("is-invalid");
        }
    });

    $("form").on("submit", function (e) {
        let phone = $("#drop_phone").val();
        let regex = /^01[3-9][0-9]{8}$/;

        if (!regex.test(phone)) {
            e.preventDefault();
            $("#phone_error").removeClass("d-none");
            $("#drop_phone").addClass("is-invalid");
        }
    });

    $("#drop_phone").on("keypress", function (e) {
        if (e.which < 48 || e.which > 57) {
            return false;
        }
    });

    document.addEventListener("input", function(e) {
        if (e.target.id === "price") {
            let value = parseFloat(e.target.value);

            if (!isNaN(value) && value >= 0) {
                document.getElementById("price_error").classList.add("d-none");
                e.target.classList.remove("is-invalid");
                e.target.classList.add("is-valid");
            } else {
                document.getElementById("price_error").classList.remove("d-none");
                e.target.classList.remove("is-valid");
                e.target.classList.add("is-invalid");
            }
        }
    });

    document.querySelector("form").addEventListener("submit", function(e) {
        let priceInput = document.getElementById("price");
        let value = parseFloat(priceInput.value);

        if (isNaN(value) || value < 0) {
            e.preventDefault();
            document.getElementById("price_error").classList.remove("d-none");
            priceInput.classList.add("is-invalid");
        }
    });
</script>

<script>
    $(document).on("input", "#drop_phone", function () {
        let phone = $(this).val();
        let regex = /^01[3-9][0-9]{8}$/;

        if (regex.test(phone)) {
            $("#phone_error").addClass("d-none");
            $(this).removeClass("is-invalid").addClass("is-valid");

            // AJAX to fetch data
            $.ajax({
                url: "{{ url('/get-dropoff-details') }}",
                method: "GET",
                data: { drop_phone: phone },
                success: function (response) {
                    if (response.success) {

                        $("input[name='drop_name']").val(response.data.drop_name);
                        $("#drop_district").val(response.data.drop_district).trigger('change');

                        setTimeout(() => {
                            $("#drop_area").val(response.data.drop_area).trigger('change');
                        }, 300);

                        $("#drop_street").val(response.data.drop_street);
                        $("#drop_address").val(response.data.drop_address);
                    }
                }
            });

        } else {
            $("#phone_error").removeClass("d-none");
            $(this).removeClass("is-valid").addClass("is-invalid");
        }
    });
</script>

@endpush
