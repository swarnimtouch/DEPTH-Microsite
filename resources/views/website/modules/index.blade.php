@extends('layouts.website')

@section('body')
    <section class="module-header-section py-4 py-lg-5">
        <div class="container">
            <div class="row align-items-end">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="breadcrumb-custom mb-2">
                        <a href="{{ route('home') }}" class="text-decoration-none text-muted breadcrumb-link">Home</a> /
                        <span class="text-dark-green fw-semibold">Programme Chapters</span>
                    </div>
                    <h1 class="fw-bolder text-dark-green mb-0">{{ $activeCourse?->title ?? 'Programme Chapters' }}</h1>
                </div>
                <div class="col-lg-6 d-flex justify-content-lg-end">
                    <div class="overall-progress-card bg-white border shadow-sm rounded-4 p-4">
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-bold text-dark-green module-text-sm">Overall Progress</span>
                            <span class="fw-bold text-primary-green module-text-sm">0/{{ $modules->count() }} Chapters Complete</span>
                        </div>
                        <div class="progress custom-progress-bar">
                            <div class="progress-bar bg-primary-green custom-progress-width-0" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="modules-grid-section pb-5">
        <div class="container">
            @if($modules->isEmpty())
                <div class="bg-white shadow-sm rounded-4 p-5 text-center">
                    <h4 class="fw-bold text-dark-green mb-2">No chapters available yet</h4>
                    <p class="text-muted mb-0">Please check back soon.</p>
                </div>
            @else
                <div class="row g-4">
                    @foreach($modules as $module)
                        @php
                            $moduleNumber = str_pad($loop->iteration, 2, '0', STR_PAD_LEFT);
                            $isAvailable = true;
                            $availabilityLabel = $isAvailable ? 'Available' : 'Disabled';
                            $moduleProgress = $progressByModule[$module->id] ?? null;
                            $progressPercent = $moduleProgress?->progress_percent ?? 0;
                            $cardClass = $isAvailable ? 'card-active shadow-lg' : 'card-locked shadow-sm';
                            $titleClass = $isAvailable ? 'text-dark-green' : 'text-muted';
                            $mutedClass = $isAvailable ? '' : 'opacity-50';
                            $thumbnail = $module->thumbnail ? asset('storage/' . $module->thumbnail) : 'https://ui-avatars.com/api/?name=' . urlencode($module->speaker_name ?: 'Doc') . '&background=random';
                        @endphp
                        <div class="col-lg-6">
                            <div class="module-card {{ $cardClass }} bg-white p-4 h-100">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="badge rounded-pill {{ $isAvailable ? 'badge-primary-green' : 'badge-locked text-muted' }}">CHAPTER {{ $moduleNumber }}</span>
                                    <span class="badge rounded-pill {{ $isAvailable ? 'badge-light-green' : 'badge-locked-status text-muted' }}">{{ $availabilityLabel }}</span>
                                </div>
                                <h4 class="fw-bold {{ $titleClass }} mb-4">{{ $module->title }}</h4>

{{--                                <div class="d-flex justify-content-between align-items-center mb-4 {{ $mutedClass }}">--}}
{{--                                    <span class="text-muted module-text-sm fw-semibold">{{ $module->duration ?: 'Chapter' }}</span>--}}
{{--                                </div>--}}

                                @if(!$isAvailable)
                                    <button class="btn btn-locked w-100 fw-semibold mb-4" disabled>Disabled</button>
                                @else
                                    <a href="{{ route('modules.show', $module->slug) }}" class="btn btn-primary-custom w-100 fw-bold mb-4 btn-watch-action">Watch Now</a>
                                @endif

                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted module-text-xs text-uppercase fw-bold">AVAILABLE</span>
                                    <span class="text-muted module-text-xs fw-bold">
                                        @if($module->start_at_utc_iso || $module->end_at_utc_iso)
                                            <span
                                                data-local-datetime-range
                                                data-local-start="{{ $module->start_at_utc_iso }}"
                                                data-local-end="{{ $module->end_at_utc_iso }}"
                                            >{{ optional($module->start_at)->format('M d, Y h:i A') }} - {{ optional($module->end_at)->format('M d, Y h:i A') }}</span>
                                        @else
                                            To be announced
                                        @endif
                                    </span>
                                </div>
                                <div class="progress custom-progress-bar {{ $isAvailable ? '' : 'opacity-50' }}">
                                    <div class="progress-bar bg-primary-green" style="width: {{ $progressPercent }}%" role="progressbar" aria-valuenow="{{ $progressPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="text-end text-muted module-text-xs fw-bold mt-1">{{ $progressPercent }}%</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="support-banner-section pb-5">
        <div class="container">
            <div class="support-banner bg-dark-green rounded-4 p-4 p-lg-5 text-white">
                <div class="row align-items-center">
                    <div class="col-lg-8 mb-4 mb-lg-0 pe-lg-5">
                        <h3 class="fw-bold mb-3">Having trouble with an chapter?</h3>
                        <p class="mb-0 text-white-50 support-banner-desc">Our academic support team is available to help with technical issues or programme content queries.</p>
                    </div>
                    <div class="col-lg-4 text-center text-lg-end mt-4 mt-lg-0">
                        <div class="d-flex flex-nowrap gap-2 gap-sm-3 justify-content-center justify-content-lg-end">
                            <a href="#" class="btn btn-white-custom px-3 px-sm-4 py-2 fw-bold text-nowrap">Contact Support</a>
                            <a href="#" class="btn btn-outline-white-custom px-3 px-sm-4 py-2 fw-semibold text-nowrap">View FAQs</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('web/js/modules.js') }}"></script>
@endpush
