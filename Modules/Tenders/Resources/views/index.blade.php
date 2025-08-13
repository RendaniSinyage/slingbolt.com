@extends('layouts.admin')

@section('page-title')
    {{ __('Tenders') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Tenders Found') }}</h5>
                    <div class="card-header-right">
                        <a href="#" data-url="{{ route('tenders.settings') }}" data-ajax-popup="true" data-title="{{__('Tender Settings')}}" data-bs-toggle="tooltip" title="{{__('Settings')}}"  class="btn btn-sm btn-primary">
                            <i class="ti ti-settings"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <p>{{ __("Good day. I have found the following tenders that match your criteria. Please review them at your convenience.") }}</p>
                    @if($tenders->isEmpty())
                        <div class="alert alert-info">
                            {{ __('No tenders found matching your criteria.') }}
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Title') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Category') }}</th>
                                        <th>{{ __('Procuring Entity') }}</th>
                                        <th>{{ __('End Date') }}</th>
                                        <th class="text-end">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tenders as $tender)
                                        <tr>
                                            <td>{{ $tender->title }}</td>
                                            <td>{{ $tender->status }}</td>
                                            <td>{{ $tender->main_procurement_category }}</td>
                                            <td>{{ $tender->procuring_entity_name }}</td>
                                            <td>{{ $tender->tender_period_end_date }}</td>
                                            <td class="text-end">
                                                <a href="{{ route('tenders.accept', $tender->id) }}" class="btn btn-sm btn-success">{{ __('Accept') }}</a>
                                                <a href="{{ route('tenders.deny', $tender->id) }}" class="btn btn-sm btn-danger">{{ __('Deny') }}</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $tenders->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
