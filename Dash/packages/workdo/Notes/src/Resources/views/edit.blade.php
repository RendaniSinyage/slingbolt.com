{{Form::model($note,array('route' => array('notes.update', $note->id), 'method' => 'PUT','class'=>'needs-validation','novalidate')) }}
    <div class="modal-body">
        <div class="text-end">
            @if (module_is_active('AIAssistant'))
                @include('aiassistant::ai.generate_ai_btn', [
                    'template_module' => 'note',
                    'module' => 'Notes',
                ])
            @endif
        </div>
        <div class="row">
            <div class="col-md-12 form-group">
                <label for="title" class="form-label">{{ __('Title') }}</label><x-required></x-required>
                <input class="form-control" type="text" id="title" name="title"
                    placeholder="{{ __('Enter Title') }}" value="{{ $note->title }}" required>
            </div>

            <div class="col-md-12 form-group">
                <label for="color" class="form-label">{{ __('Color') }}</label><x-required></x-required>
                <select class="form-control" name="color" id="color" required>
                    <option value="bg-primary">{{ __('Primary') }}</option>
                    <option @if ($note->color == 'bg-secondary') selected @endif value="bg-secondary">{{ __('Secondary') }}
                    </option>
                    <option @if ($note->color == 'bg-info') selected @endif value="bg-info">{{ __('Info') }}
                    </option>
                    <option @if ($note->color == 'bg-warning') selected @endif value="bg-warning">{{ __('Warning') }}
                    </option>
                    <option @if ($note->color == 'bg-danger') selected @endif value="bg-danger">{{ __('Danger') }}
                    </option>
                </select>
            </div>

            <div class="col-md-12 form-group">
                <label for="type" class="form-label">{{ __('Type') }}</label>
                <div class="selectgroup w-50 ">

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="type" value="personal" id="personal"
                            {{ $note->type == 'personal' ? 'checked="checked"' : '' }}>
                        <label class="form-check-label" for="personal">
                            {{ __('Personal') }}
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="type" value="shared" id="shared"
                            {{ $note->type == 'shared' ? 'checked="checked"' : '' }}>
                        <label class="form-check-label" for="shared">
                            {{ __('Shared') }}
                        </label>
                    </div>
                </div>
            </div>

            <div class="col-md-12 assign_to_selection">
                <label for="assign_to" class="form-label">{{ __('Assign to') }}</label>
                <select id="assign_to"name="assign_to[]" class="multi-select" data-toggle="select2" multiple="multiple"
                    data-placeholder="{{ __('Select Users ...') }}">
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}" @if (in_array($u->id, $note->assign_to)) selected @endif>
                            {{ $u->name }} - {{ $u->email }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-12 form-group">
                <label for="description" class="form-label">{{ __('Description') }}</label><x-required></x-required>
                <textarea class="form-control" id="description" rows="3" name="text" placeholder="{{ __('Enter Description') }}" required>{{ $note->text }}</textarea>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <div>
            <button type="button" class="btn btn-light me-1" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
            <button class="btn btn-primary" type="submit" id="create-client">{{ __('Update') }}</button>
        </div>
    </div>
{{Form::close()}}
<script>
    $(document).ready(function() {
        $('#{{ $note->type }}').trigger('click');
    });
</script>
<script type="text/javascript">
    if ($(".multi-select").length > 0) {
        $($(".multi-select")).each(function(index, element) {
            var id = $(element).attr('id');
            var multipleCancelButton = new Choices(
                '#' + id, {
                    removeItemButton: true,
                }
            );
        });
    }
</script>
