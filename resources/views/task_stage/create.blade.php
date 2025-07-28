{{ Form::open(array('url' => 'project-task-new-stage', 'class'=>'needs-validation', 'novalidate')) }}
<div class="modal-body">

    <div class="row">
        <div class="form-group col-12">
            {{ Form::label('name', __('Project Task Stage Name'),['class'=>'form-label']) }}<x-required></x-required>
            {{ Form::text('name', '', array('class' => 'form-control','required'=>'required', 'placeholder'=>__('Enter Project Task Stage Name'))) }}
        </div>
    <div class="form-group col-12">
        {{ Form::label('type', __('Project Type'),['class'=>'form-label']) }}<x-required></x-required>
       {{ Form::select('type', $projectTypes, \App\Models\ProjectType::STANDARD, array('class' => 'form-control','required'=>'required', 'placeholder'=>__('Select Project Type'))) }}
    </div>
        <div class="form-group col-12">
            {{ Form::label('color', __('Color'),['class'=>'form-label']) }}<x-required></x-required>
            <input class="jscolor form-control" value="FFFFFF" name="color" id="color" required>
            <small class="small">{{ __('For chart representation') }}</small>
        </div>

    </div>
</div>
<div class="modal-footer">
        <input type="button" value="{{__('Cancel')}}" class="btn  btn-secondary" data-bs-dismiss="modal">
        <input type="submit" value="{{__('Create')}}" class="btn  btn-primary">
</div>
{{ Form::close() }}
