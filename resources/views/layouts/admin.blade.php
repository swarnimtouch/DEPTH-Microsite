@extends('layouts.master')

@section('body')
    @include('partials.header')
    @include('partials.toolbar')
    @yield('content')
    @include('partials.footer')
@endsection
