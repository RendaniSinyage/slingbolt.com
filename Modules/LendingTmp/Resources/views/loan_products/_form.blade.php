<div class="form-group">
    <label for="product_code">Product Code</label>
    <input type="text" name="product_code" id="product_code" class="form-control" value="{{ old('product_code', $product->product_code ?? '') }}" required>
</div>

<div class="form-group">
    <label for="product_name">Product Name</label>
    <input type="text" name="product_name" id="product_name" class="form-control" value="{{ old('product_name', $product->product_name ?? '') }}" required>
</div>

<div class="form-group">
    <label for="rate_of_interest">Rate of Interest (%)</label>
    <input type="number" step="0.01" name="rate_of_interest" id="rate_of_interest" class="form-control" value="{{ old('rate_of_interest', $product->rate_of_interest ?? '') }}" required>
</div>

<div class="form-group">
    <label for="penalty_interest_rate">Penalty Interest Rate (%)</label>
    <input type="number" step="0.01" name="penalty_interest_rate" id="penalty_interest_rate" class="form-control" value="{{ old('penalty_interest_rate', $product->penalty_interest_rate ?? '') }}">
</div>

<div class="form-group">
    <label for="maximum_loan_amount">Maximum Loan Amount</label>
    <input type="number" step="0.01" name="maximum_loan_amount" id="maximum_loan_amount" class="form-control" value="{{ old('maximum_loan_amount', $product->maximum_loan_amount ?? '') }}">
</div>

<div class="form-check">
    <input type="hidden" name="is_term_loan" value="0">
    <input type="checkbox" name="is_term_loan" id="is_term_loan" class="form-check-input" value="1" {{ old('is_term_loan', $product->is_term_loan ?? false) ? 'checked' : '' }}>
    <label class="form-check-label" for="is_term_loan">Is Term Loan?</label>
</div>

<div class="form-check">
    <input type="hidden" name="disabled" value="0">
    <input type="checkbox" name="disabled" id="disabled" class="form-check-input" value="1" {{ old('disabled', $product->disabled ?? false) ? 'checked' : '' }}>
    <label class="form-check-label" for="disabled">Disabled</label>
</div>

<button type="submit" class="btn btn-primary">Submit</button>
