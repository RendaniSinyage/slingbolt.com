{{ Form::open(['route' => 'tenders.settings.store', 'method' => 'POST']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('categories', __('Categories'), ['class' => 'form-label']) }}
                {{ Form::select('categories[]', ['Construction' => 'Construction', 'IT' => 'IT', 'Consulting' => 'Consulting'], isset($settings) ? json_decode($settings->categories) : null, ['class' => 'form-control', 'multiple' => 'multiple', 'id' => 'categories']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('provinces', __('Provinces'), ['class' => 'form-label']) }}
                {{ Form::select('provinces[]', ['Gauteng' => 'Gauteng', 'Western Cape' => 'Western Cape', 'KwaZulu-Natal' => 'KwaZulu-Natal'], isset($settings) ? json_decode($settings->provinces) : null, ['class' => 'form-control', 'multiple' => 'multiple', 'id' => 'provinces']) }}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('type', __('Type'), ['class' => 'form-label']) }}
                {{ Form::select('type', ['' => 'All', 'Open Tender' => 'Open Tender', 'Closed Tender' => 'Closed Tender'], isset($settings) ? $settings->type : null, ['class' => 'form-control']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('submission_type', __('Submission Type'), ['class' => 'form-label']) }}
                {{ Form::select('submission_type', ['esubmission' => 'eSubmission', 'manual' => 'Manual'], isset($settings) ? $settings->submission_type : 'esubmission', ['class' => 'form-control']) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    {{ Form::submit(__('Save'), ['class' => 'btn  btn-primary']) }}
</div>
{{ Form::close() }}
