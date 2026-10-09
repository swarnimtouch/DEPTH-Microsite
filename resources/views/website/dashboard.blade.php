@extends('layouts.dashboard')

@php
    $doctorName = $doctor->name ?? 'Doctor';
    $firstName = explode(' ', trim($doctorName))[0] ?: 'Doctor';
    $today = now()->format('l, F j, Y');
    $hour = now()->format('H');
    $greeting = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');
    $coursePercent = $totalModules ? round(($completedModules / $totalModules) * 100) : 0;
    $certificateStatus = $totalModules && $completedModules === $totalModules ? 'Ready' : 'In Progress';
@endphp

@section('content')
    <div class="row align-items-end mb-4 g-3">
        <div class="col-lg-6">
            <h2 class="fw-bolder text-dark-green mb-1">{{ $greeting }}, Dr. {{ $firstName }}</h2>
            <p class="text-muted mb-0">Today is {{ $today }}</p>
        </div>
        <div class="col-lg-6 d-flex justify-content-lg-end">
            <div class="course-tracker-widget text-end">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-bold text-primary-green tracker-label">PROGRAMME PROGRESS</span>
                    <span class="fw-bold text-dark-green tracker-label text-end">{{ $completedModules }} of {{ $totalModules }} Chapters Complete</span>
                </div>
                <div class="d-flex align-items-center justify-content-end course-timeline-dots">
                    @forelse($modules as $module)
                        @php $isDone = ($progress[$module->id]->progress_percent ?? 0) === 100; @endphp
                        <span class="dot {{ $isDone ? 'done' : 'pending' }}"></span>
                        @if(!$loop->last)
                            <span class="line {{ $isDone ? 'done' : 'pending' }}"></span>
                        @endif
                    @empty
                        <span class="dot pending"></span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card bg-light-green p-4 rounded-4 h-100 position-relative">
                <div class="stat-icon-box shadow-sm text-primary-green">
                    <i class="bi bi-bookmark-check"></i>
                </div>
                <span class="stat-badge text-primary-green fw-bold position-absolute top-0 end-0 mt-4 me-4">{{ $coursePercent }}%</span>
                <span class="d-block text-dark-green fw-medium mb-1">Chapters Completed</span>
                <h2 class="fw-bolder text-dark-green mb-0">{{ str_pad($completedModules, 2, '0', STR_PAD_LEFT) }} <span class="text-muted fs-5 fw-normal">/ {{ str_pad($totalModules, 2, '0', STR_PAD_LEFT) }}</span></h2>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card bg-light-purple p-4 rounded-4 h-100 position-relative">
                <div class="stat-icon-box shadow-sm text-purple">
                    <i class="bi bi-patch-question"></i>
                </div>
                <span class="stat-badge text-purple fw-bold position-absolute top-0 end-0 mt-4 me-4">{{ $feedbackSubmitted ? 'Updated' : 'No feedback' }}</span>
                <span class="d-block text-dark-purple fw-medium mb-1">Feedback Submitted</span>
                <h2 class="fw-bolder text-purple mb-0">{{ $feedbackSubmitted }}</h2>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card bg-light-green p-4 rounded-4 h-100 position-relative">
                <div class="stat-icon-box shadow-sm text-primary-green">
                    <i class="bi bi-calendar-event"></i>
                </div>
                <span class="d-block text-dark-green fw-medium mb-1">Next Chapter</span>
                <h4 class="fw-bold text-dark-green mb-0 mt-2">
                    @if($nearestModule?->start_at_utc_iso)
                        <span data-local-datetime="{{ $nearestModule->start_at_utc_iso }}">{{ $nearestModule->start_at->format('M d, h:i A') }}</span>
                    @else
                        Not Scheduled
                    @endif
                </h4>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card bg-light-purple p-4 rounded-4 h-100 position-relative">
                <div class="stat-icon-box shadow-sm text-purple">
                    <i class="bi bi-award"></i>
                </div>
                <span class="d-block text-dark-purple fw-medium mb-1">Certificate Status</span>
                <h4 class="fw-bold text-purple mb-0 mt-2">{{ $certificateStatus }}</h4>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-4 col-lg-12">
            <div class="bg-white p-4 rounded-4 shadow-sm h-100">
                <h6 class="fw-bold text-dark-green mb-4">Recent Activity</h6>
                <div class="activity-feed">
                    @forelse($recentActivity as $activity)
                        <div class="activity-item d-flex {{ !$loop->last ? 'mb-4' : '' }}">
                            <div class="activity-dot-wrapper d-flex flex-column align-items-center me-3">
                                <div class="activity-dot bg-primary-green rounded-circle"></div>
                                @if(!$loop->last)
                                    <div class="activity-line flex-grow-1 mt-1"></div>
                                @endif
                            </div>
                            <div class="pb-2">
                                <h6 class="fw-bold text-dark-green mb-1 fs-6">{{ $activity['title'] }}</h6>
                                <span class="text-muted small">{{ $activity['date']->format('d M Y, h:i A') }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted small">No activity yet.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-12">
            <div class="live-session-card p-4 p-lg-5 rounded-4 h-100 position-relative overflow-hidden text-white">
                <span class="badge rounded-pill live-badge mb-3">{{ $nearestModule?->isAvailable() ? 'AVAILABLE CHAPTER' : 'UPCOMING CHAPTER' }}</span>
                <div class="row align-items-center">
                    <div class="col-md-7 position-relative z-1">
                        <h3 class="fw-bold mb-3">{{ $nearestModule?->title ?? 'No chapter scheduled' }}</h3>
                        <p class="mb-4 opacity-75 small">
                            @if($nearestModule)
                                <span
                                    data-local-datetime-range
                                    data-local-start="{{ $nearestModule->start_at_utc_iso }}"
                                    data-local-end="{{ $nearestModule->end_at_utc_iso }}"
                                >{{ $nearestModule->start_at?->format('F d, Y h:i A') }} - {{ $nearestModule->end_at?->format('F d, Y h:i A') }}</span>
                            @else
                                Add chapters from admin to show the next learning session.
                            @endif
                        </p>

                        @if($nearestModule)
{{--                            <div class="d-flex align-items-center mb-4">--}}
{{--                                <img src="https://ui-avatars.com/api/?name={{ urlencode($nearestModule->speaker_name ?: 'Faculty') }}&background=random" alt="Speaker" class="rounded-circle me-3 border border-2 border-white" width="40" height="40">--}}
{{--                                <div>--}}
{{--                                    <h6 class="fw-bold mb-0 text-white">{{ $nearestModule->speaker_name ?: 'EPIC Faculty' }}</h6>--}}
{{--                                    <span class="small opacity-75">{{ $nearestModule->speaker_designation ?: 'Faculty' }}</span>--}}
{{--                                </div>--}}
{{--                            </div>--}}
                        @endif

                        @if($nearestModule)
                            <a href="{{ route('modules.show', $nearestModule->slug) }}" class="btn btn-white-custom fw-semibold px-4">Open Chapter</a>
                        @else
                            <a href="{{ route('modules') }}" class="btn btn-white-custom fw-semibold px-4">View Chapters</a>
                        @endif
                    </div>
                    <div class="col-md-5 d-none d-md-block position-relative z-1">
                        <div class="live-card-graphic rounded-3 border border-white border-opacity-25 bg-white bg-opacity-10 h-100 w-100 min-vh-25"></div>
                    </div>
                </div>
                <div class="live-card-bg-shape position-absolute rounded-circle"></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="bg-white p-4 rounded-4 shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                    <div>
                        <h5 class="fw-bold text-dark-green mb-1">Feedback Performance Analysis</h5>
                        <span class="text-muted small d-block">Scores across completed medical chapters</span>
                    </div>
                    <div class="custom-chart-legend d-flex gap-3 mt-2 mt-sm-0">
                        <div class="d-flex align-items-center">
                            <span class="legend-dot green-dot me-2"></span>
                            <span class="text-muted small">Your Score</span>
                        </div>
                    </div>
                </div>

                <div class="chart-container w-100">
                    <canvas id="quizChart"></canvas>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('dashboard_scripts')
    <script>
        window.dashboardQuizChart = {
            labels: @json($chartLabels),
            scores: @json($chartScores),
        };
    </script>
@endpush
