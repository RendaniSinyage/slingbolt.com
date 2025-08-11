<div class="form-group">
    <label for="loan_security_code">Security Code</label>
    <input type="text" name="loan_security_code" id="loan_security_code" class="form-control" value="{{ old('loan_security_code', $security->loan_security_code ?? '') }}" required>
</div>

<div class="form-group">
    <label for="loan_security_name">Security Name</label>
    <input type="text" name="loan_security_name" id="loan_security_name" class="form-control" value="{{ old('loan_security_name', $security->loan_security_name ?? '') }}" required>
</div>

<div class="form-group">
    <label for="loan_security_type_id">Security Type</label>
    <select name="loan_security_type_id" id="loan_security_type_id" class="form-control" required>
        @foreach($types as $type)
            <option value="{{ $type->id }}" {{ (old('loan_security_type_id', $security->loan_security_type_id ?? '') == $type->id) ? 'selected' : '' }}>
                {{ $type->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label for="original_security_value">Original Value</label>
    <input type="number" step="0.01" name="original_security_value" id="original_security_value" class="form-control" value="{{ old('original_security_value', $security->original_security_value ?? '') }}" required>
</div>

<button type="submit" class="btn btn-primary">Submit</button>
