@extends('layouts.main')
@section('page-title')
    {{ __('Manage Notes') }}
@endsection

@section('page-breadcrumb')
    {{ __('Notes') }}
@endsection

@section('page-action')
    @permission('note create')
        <div class="d-flex">
            @stack('addButtonHook')
            <a href="#" class="btn btn-sm btn-primary" data-toggle="tooltip" title="{{ __('Create') }}" data-ajax-popup="true"
                data-size="lg" data-title="{{ __('Create Note') }}" data-url="{{ route('notes.create') }}">
                <i class="ti ti-plus"></i>
            </a>
        </div>
    @endpermission
@endsection
@push('css')
    <style>
        .note_color_dot {
            border-radius: 7px;
            height: 10px;
            width: 10px;
            position: absolute;
            margin: 30px 10px;
        }
        .notes-card .card-body {
            padding: 30px 10px 30px 30px;
            height: 210px;
            overflow: hidden;
        }
        .notes-card .card-body .note-text {
            height: 100%;
            overflow-y: auto;
            padding: 0 20px 0 0
        }
        .notes-card .card-body .note-text::-webkit-scrollbar {
            width: 5px;
        }
        .notes-card .card-body .note-text::-webkit-scrollbar-thumb {
            background: #060606;
            border-radius: 10px;
        }
        .notes-card .card-body .note-text::-webkit-scrollbar-track {
            background: var(--bs-gray-400);
            border-radius: 10px;
        }
    </style>
@endpush
@section('content')
    @if (getActiveWorkSpace())
        <div class="row justify-content-between align-items-center">
            <div
                class="col-xl-5 col-lg-4 col-md-12 d-flex align-items-center justify-content-between justify-content-md-start">
                <div class="d-inline-block">
                    <h5 class="h4 d-inline-block font-weight-400">{{ __('Personal Notes') }}</h5>
                </div>
            </div>
        </div>
        <section class="section">
            @if (count($personal_notes) > 0)
                <div class="row">
                    <div class="col-lg-12">
                        <div class="row notes-list">
                            @foreach ($personal_notes as $note)
                                <div class="col-md-4 d-flex">
                                    <div class="card notes-card w-100">
                                        <div class="{{ $note->color }} note_color_dot px-1"></div>
                                        <div class="card-header">
                                            <h5 class="">{{ $note->title }}</h5>
                                            <div class="card-header-right">
                                                <div class="btn-group card-option">
                                                    <button type="button" class="btn dropdown-toggle"
                                                        data-bs-toggle="dropdown" aria-haspopup="true"
                                                        aria-expanded="false">
                                                        <i class="feather icon-more-vertical"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        @permission('note edit')
                                                            <a href="#" class="dropdown-item" data-ajax-popup="true"
                                                                data-size="lg" data-title="{{ __('Edit Note') }}"
                                                                data-url="{{ route('notes.edit', [$note->id]) }}">
                                                                <i class="ti ti-pencil"></i> <span>{{ __('Edit') }}</span>
                                                            </a>
                                                        @endpermission
                                                        @permission('note delete')
                                                            {{ Form::open(['route' => ['notes.destroy', $note->id]]) }}
                                                            @method('DELETE')
                                                            <a href="#" class="dropdown-item bs-pass-para show_confirm"
                                                                data-confirm="{{ __('Are You Sure?') }}"
                                                                data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                data-confirm-yes="delete-form-{{ $note->id }}">
                                                                <span class="text-danger"><i class="ti ti-trash"></i> {{ __('Delete') }}</span>
                                                            </a>
                                                            {{ Form::close() }}
                                                        @endpermission
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="scrollText note-text">
                                                {{ $note->text }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body p-4">
                        <div class="page-error">
                            <div class="page-inner">
                                <div class="page-description">
                                    {{ __('No Personal Notes available') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </section>

        <div class="row justify-content-between align-items-center">
            <div
                class="col-xl-5 col-lg-4 col-md-12 d-flex align-items-center justify-content-between justify-content-md-start">
                <div class="d-inline-block">
                    <h5 class="h4 d-inline-block font-weight-400">{{ __('Shared Notes') }}</h5>
                </div>
            </div>
        </div>
        <section class="section">
            @if (count($shared_notes) > 0)
                <div class="row">
                    <div class="col-lg-12">
                        <div class="row notes-list">
                            @foreach ($shared_notes as $note)
                                <div class="col-md-4 d-flex">
                                    <div class="card notes-card w-100">
                                        <div class="{{ $note->color }} note_color_dot px-1"></div>
                                        <div class="card-header">
                                            <h5 class="">{{ $note->title }}</h5>
                                            <div class="card-header-right">
                                                <div class="btn-group card-option">
                                                    <button type="button" class="btn dropdown-toggle"
                                                        data-bs-toggle="dropdown" aria-haspopup="true"
                                                        aria-expanded="false">
                                                        <i class="feather icon-more-vertical"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        @permission('note edit')
                                                            <a href="#" class="dropdown-item" data-ajax-popup="true"
                                                                data-size="lg" data-title="{{ __('Edit Note') }}"
                                                                data-url="{{ route('notes.edit', [$note->id]) }}">
                                                                <i class="ti ti-pencil"></i> <span>{{ __('Edit') }}</span>
                                                            </a>
                                                        @endpermission
                                                        @permission('note delete')
                                                            @if ($note->created_by == Auth::user()->id)
                                                                {{ Form::open(['route' => ['notes.destroy', $note->id]]) }}
                                                                @method('DELETE')

                                                                <a href="#"
                                                                    class="dropdown-item bs-pass-para show_confirm"
                                                                    data-confirm="{{ __('Are You Sure?') }}"
                                                                    data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                    data-confirm-yes="delete-form-{{ $note->id }}">
                                                                    <span class="text-danger"><i class="ti ti-trash"></i>
                                                                    {{ __('Delete') }}</span>
                                                                </a>
                                                                {{ Form::close() }}
                                                            @endif
                                                        @endpermission
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body "style=" height: 210px !important; overflow:auto">
                                            <div class="note-text">
                                                {{ $note->text }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body p-4">
                        <div class="page-error">
                            <div class="page-inner">
                                <div class="page-description">
                                    {{ __('No Shared Notes available') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </section>
    @else
        <div class="container mt-5">
            <div class="card">
                <div class="card-body p-4">
                    <div class="page-error">
                        <div class="page-inner">
                            <h1>404</h1>
                            <div class="page-description">
                                {{ __('Page Not Found') }}
                            </div>
                            <div class="page-search">
                                <p class="text-muted mt-3">
                                    {{ __("It's looking like you may have taken a wrong turn. Don't worry... it happens to the best of us. Here's a little tip that might help you get back on track.") }}
                                </p>
                                <div class="mt-3">
                                    <a class="btn-return-home badge-blue" href="{{ route('home') }}"><i
                                            class="fas fa-reply"></i> {{ __('Return Home') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        $(document).on('click', 'input[name="type"]', function() {
            var type = $('input[name="type"]:checked').val();
            if (type == 'shared') {
                $('.assign_to_selection').show();
            } else {
                $('.assign_to_selection').hide();
            }
        });
    </script>
@endpush
