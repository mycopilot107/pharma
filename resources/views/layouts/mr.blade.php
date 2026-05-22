@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-lg">
    @include('partials.mr-nav')
    @yield('mr-content')
</div>
@endsection

@push('head')
@include('partials.mr-live-tracker')
@endpush
