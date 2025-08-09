@extends('layouts.admin')

@section('page-title')
    {{ __('Lending Management') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Lending')}}</li>
@endsection

@section('content')
    @yield('lending-content')
@endsection
