@extends('layouts.admin')

@section('page-title')
    {{ __('Tender Settings') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Tender Settings') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('tenders.settings.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="categories">{{ __('Categories') }}</label>
                                    <select name="categories[]" id="categories" class="form-control" multiple>
                                        {{-- These should be populated from a reliable source later --}}
                                        <option value="Construction" @if(in_array('Construction', json_decode($settings->categories ?? '[]'))) selected @endif>Construction</option>
                                        <option value="IT" @if(in_array('IT', json_decode($settings->categories ?? '[]'))) selected @endif>IT</option>
                                        <option value="Consulting" @if(in_array('Consulting', json_decode($settings->categories ?? '[]'))) selected @endif>Consulting</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="provinces">{{ __('Provinces') }}</label>
                                    <select name="provinces[]" id="provinces" class="form-control" multiple>
                                        {{-- These should be populated from a reliable source later --}}
                                        <option value="Gauteng" @if(in_array('Gauteng', json_decode($settings->provinces ?? '[]'))) selected @endif>Gauteng</option>
                                        <option value="Western Cape" @if(in_array('Western Cape', json_decode($settings->provinces ?? '[]'))) selected @endif>Western Cape</option>
                                        <option value="KwaZulu-Natal" @if(in_array('KwaZulu-Natal', json_decode($settings->provinces ?? '[]'))) selected @endif>KwaZulu-Natal</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="type">{{ __('Type') }}</label>
                                    <select name="type" id="type" class="form-control">
                                        <option value="">{{ __('All') }}</option>
                                        <option value="Open Tender" @if($settings->type == 'Open Tender') selected @endif>Open Tender</option>
                                        <option value="Closed Tender" @if($settings->type == 'Closed Tender') selected @endif>Closed Tender</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="submission_type">{{ __('Submission Type') }}</label>
                                    <select name="submission_type" id="submission_type" class="form-control">
                                        <option value="esubmission" @if($settings->submission_type == 'esubmission') selected @endif>eSubmission</option>
                                        <option value="manual" @if($settings->submission_type == 'manual') selected @endif>Manual</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 text-end">
                                <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
