{{-- For simplicity, this form currently only supports 'Customer' as an applicant type --}}
<div class="form-group">
    <label for="applicant_id">Applicant (Customer)</label>
    <select name="applicant_id" id="applicant_id" class="form-control" required>
        @foreach($customers as $customer)
            <option value="{{ $customer->id }}" {{ (old('applicant_id', $loan->applicant_id ?? '') == $customer->id) ? 'selected' : '' }}>
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
            <option value="{{ $product->id }}" {{ (old('loan_product_id', $loan->loan_product_id ?? '') == $product->id) ? 'selected' : '' }}>
                {{ $product->product_name }}
            </option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label for="loan_amount">Loan Amount</label>
    <input type="number" step="0.01" name="loan_amount" id="loan_amount" class="form-control" value="{{ old('loan_amount', $loan->loan_amount ?? '') }}" required>
</div>

<div class="form-group">
    <label for="posting_date">Posting Date</label>
    <input type="date" name="posting_date" id="posting_date" class="form-control" value="{{ old('posting_date', isset($loan) ? $loan->posting_date->format('Y-m-d') : date('Y-m-d')) }}" required>
</div>

<div class="form-group">
    <label for="disbursement_date">Disbursement Date</label>
    <input type="date" name="disbursement_date" id="disbursement_date" class="form-control" value="{{ old('disbursement_date', isset($loan) ? optional($loan->disbursement_date)->format('Y-m-d') : '') }}">
    <small>Required if status is 'Disbursed'.</small>
</div>

<div class="form-group">
    <label for="status">Status</label>
    <select name="status" id="status" class="form-control" required>
        <option value="Sanctioned" {{ (old('status', $loan->status ?? 'Sanctioned') == 'Sanctioned') ? 'selected' : '' }}>Sanctioned</option>
        <option value="Disbursed" {{ (old('status', $loan->status ?? '') == 'Disbursed') ? 'selected' : '' }}>Disbursed</option>
        @if(isset($loan))
        <option value="Closed" {{ (old('status', $loan->status) == 'Closed') ? 'selected' : '' }}>Closed</option>
        @endif
    </select>
</div>

<button type="submit" class="btn btn-primary">Submit</button>
