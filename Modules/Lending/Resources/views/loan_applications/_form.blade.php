{{-- For simplicity, this form currently only supports 'Customer' as an applicant type --}}
<div class="form-group">
    <label for="applicant_id">Applicant (Customer)</label>
    <select name="applicant_id" id="applicant_id" class="form-control" required>
        @foreach($customers as $customer)
            <option value="{{ $customer->id }}" {{ (old('applicant_id', $application->applicant_id ?? '') == $customer->id) ? 'selected' : '' }}>
                {{ $customer->name }}
            </option>
        @endforeach
    </select>
    <input type="hidden" name="applicant_type" value="App\Models\Customer">
</div>

<div class="form-group">
    <label for="loan_product_id">Loan Product</label>
    <select name="loan_product_id" id="loan_product_id" class="form-control" required>
        @foreach($loanProducts as $product)
            <option value="{{ $product->id }}" {{ (old('loan_product_id', $application->loan_product_id ?? '') == $product->id) ? 'selected' : '' }}>
                {{ $product->product_name }}
            </option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label for="loan_amount">Loan Amount</label>
    <input type="number" step="0.01" name="loan_amount" id="loan_amount" class="form-control" value="{{ old('loan_amount', $application->loan_amount ?? '') }}" required>
</div>

<div class="form-check">
    <input type="hidden" name="is_secured_loan" value="0">
    <input type="checkbox" name="is_secured_loan" id="is_secured_loan" class="form-check-input" value="1" {{ old('is_secured_loan', $application->is_secured_loan ?? false) ? 'checked' : '' }}>
    <label class="form-check-label" for="is_secured_loan">Is Secured Loan?</label>
</div>

<div class="form-group" id="securities_section" style="display: {{ old('is_secured_loan', $application->is_secured_loan ?? false) ? 'block' : 'none' }};">
    <label for="securities">Pledge Securities</label>
    <select name="securities[]" id="securities" class="form-control" multiple>
        @foreach($securities as $security)
            <option value="{{ $security->id }}">{{ $security->loan_security_name }} (Value: {{ $security->original_security_value }})</option>
        @endforeach
    </select>
</div>


@if(isset($application))
<div class="form-group">
    <label for="status">Status</label>
    <select name="status" id="status" class="form-control" required>
        <option value="Open" {{ (old('status', $application->status) == 'Open') ? 'selected' : '' }}>Open</option>
        <option value="Approved" {{ (old('status', $application->status) == 'Approved') ? 'selected' : '' }}>Approved</option>
        <option value="Rejected" {{ (old('status', $application->status) == 'Rejected') ? 'selected' : '' }}>Rejected</option>
    </select>
</div>
@endif


<button type="submit" class="btn btn-primary">Submit</button>

@section('scripts')
<script>
    document.getElementById('is_secured_loan').addEventListener('change', function() {
        document.getElementById('securities_section').style.display = this.checked ? 'block' : 'none';
    });
</script>
@endsection
