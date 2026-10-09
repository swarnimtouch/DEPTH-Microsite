@extends('layouts.dashboard')

@section('dashboard_header_center')
    <div>
        <h6 class="fw-bold text-dark-green mb-0">{{ $activeCourse?->title ?? 'No active programme' }}</h6>
    </div>
@endsection

@section('content')
    @if(!$activeCourse)
        <div class="bg-white shadow-sm rounded-4 p-5 text-center">
            <h4 class="fw-bold text-dark-green mb-2">No active programme available</h4>
            <p class="text-muted mb-0">Please check back soon.</p>
        </div>
    @else

        <div class="row align-items-end mb-4 g-3">
            <div class="col-lg-8">
                <h3 class="fw-bolder text-dark-green mb-2">{{ $activeCourse->title }}</h3>
                @if($activeCourse->subtitle)
                    <h5 class="fw-bold text-purple mb-0">{{ $activeCourse->subtitle }}</h5>
                @endif
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="{{ route('modules') }}" class="btn btn-primary-custom px-4 py-2 fw-semibold">Start Learning</a>
            </div>
        </div>

        @include('website.partials.course_overview')

    @endif
@endsection
