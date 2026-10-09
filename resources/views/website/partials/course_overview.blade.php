@php
    $coursePeople = collect($activeCourse?->speakers ?: [])
        ->map(fn($person) => [
            'type' => $person['type'] ?? 'speaker',
            'image' => $person['image'] ?? null,
            'name' => trim($person['name'] ?? ''),
            'designation' => trim($person['designation'] ?? '') ?: '',
            'hospital' => trim($person['hospital'] ?? ''),
        ])
        ->filter(fn($person) => filled($person['name']))
        ->values();

    $modulePeople = collect($modules ?? [])
        ->map(function ($module) {
            $name = $module->speaker_name ?: $module->doctor?->name;
            return [
                'type' => 'speaker',
                'image' => null,
                'name' => $name,
                'designation' => $module->speaker_designation ?: '',
                'hospital' => $module->speaker_hospital ?: $module->doctor?->hospital,
            ];
        })
        ->filter(fn($person) => filled($person['name']))
        ->unique(fn($person) => \Illuminate\Support\Str::lower($person['name'] . '|' . $person['designation'] . '|' . $person['hospital']))
        ->values();

    $coursePeople = $coursePeople->isNotEmpty() ? $coursePeople : $modulePeople;
    $peopleGroups = [
        'speaker' => 'Speakers',
        'convenor' => 'Convenors',
        'coordinator' => 'Coordinators',
    ];
    $moduleCount = collect($modules ?? [])->count();
    $facultyCount = $coursePeople->count();
@endphp
<div class="row g-4 mb-5">
    <div class="col-12">
        <div class="course-about-panel bg-white shadow-sm">
            <div class="row g-4 align-items-center">
                <div class="col-lg-12">
                    <h5 class="fw-bolder text-dark-green mb-3">About the Programme</h5>
                    <div class="text-muted lh-lg course-about-copy">{!! nl2br(e($activeCourse->about)) !!}</div>
                </div>
            </div>
        </div>
    </div>
</div>

@foreach($peopleGroups as $type => $label)
    @php $people = $coursePeople->where('type', $type)->values(); @endphp
    @if($people->isNotEmpty())
        <div class="course-people-section mb-5">
            <div class="course-section-heading text-center mb-4">
                <span class="course-section-line"></span>
                <h4 class="fw-bolder text-dark-green mb-1">{{ $label }}</h4>
            </div>
            <div class="row g-4 justify-content-center">
                @foreach($people as $person)
                    @php
                        $storedImage = $person['image'] ?? null;
                        $hasStoredImage = filled($storedImage) && \Illuminate\Support\Facades\Storage::disk('public')->exists($storedImage);
                        $isLeySander = \Illuminate\Support\Str::contains(\Illuminate\Support\Str::lower($person['name']), 'sander');
                        $personImageUrl = $hasStoredImage
                            ? asset('storage/' . $storedImage)
                            : ($isLeySander ? asset('web/image/epic/speakers/sander.jpg') : null);
                        $personInitials = collect(preg_split('/\s+/', trim($person['name'])))
                            ->filter()
                            ->take(2)
                            ->map(fn($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                            ->join('');
                    @endphp
                    <div class="col-xl-3 col-lg-4 col-sm-6">
                        <div class="course-person-card text-center h-100">
                            <div class="course-person-photo-wrap mx-auto mb-3">
                                <span class="course-person-initials" aria-hidden="true">{{ $personInitials ?: 'HP' }}</span>
                                @if($personImageUrl)
                                    <img src="{{ $personImageUrl }}" alt="{{ $person['name'] }}" class="course-person-photo" onerror="this.remove()">
                                @endif
                            </div>
                            <h5 class="fw-bold text-dark-green mb-1">{{ $person['name'] }}</h5>
                            @if($person['designation'])
                                <div class="speaker-designation mb-2">{{ $person['designation'] }}</div>
                            @endif
                            @if($person['hospital'])
                                <p class="course-person-meta mb-0">{{ $person['hospital'] }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endforeach

<div class="row g-4 align-items-stretch">
    <div class="col-xl-8">
        <div class="course-agenda-card bg-white shadow-sm h-100">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                <div>
                    <span class="badge-custom d-inline-block px-3 py-2 mb-3 rounded-pill fw-bold">AGENDA</span>
                    <h4 class="fw-bolder text-dark-green mb-0">Programme Schedule</h4>
                </div>
                <div class="course-agenda-count">{{ $moduleCount }} Chapters</div>
            </div>
            @if(collect($modules ?? [])->isNotEmpty())
                <div class="table-responsive course-agenda-table-wrap">
                    <table class="table align-middle course-agenda-table mb-0">
                        <thead>
                        <tr>
                            <th>Chapter</th>
                            <th>Chapter Topic</th>
                            <th>Date</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($modules as $module)
                            @php
                                $moduleSpeakerNames = collect($module->speakers ?: [])
                                    ->pluck('name')
                                    ->filter()
                                    ->join(', ');
                            @endphp
                            <tr>
                                <td><span class="course-agenda-number">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span></td>
                                <td class="fw-semibold text-dark-green">{{ $module->title }}</td>
                                <td>
                                    @if($module->start_at_utc_iso)
                                        <span data-local-datetime="{{ $module->start_at_utc_iso }}">{{ $module->start_at->format('M d, Y h:i A') }}</span>
                                    @else
                                        To be announced
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-muted">Agenda will be displayed after chapters are added.</div>
            @endif
        </div>
    </div>

    <div class="col-xl-4">
        <div class="live-session-card course-next-card p-4 h-100 text-white">
            <span class="badge rounded-pill live-badge mb-3">PROGRAMME CHAPTERS</span>
            <h3 class="fw-bold mb-3">{{ $moduleCount }} Learning Chapters</h3>
            <p class="opacity-75 small mb-4">Continue the active programme curriculum and submit feedback for each chapter.</p>

            <div class="course-next-session p-3 mb-4">
                <span class="d-block small opacity-75 mb-1">Nearest Live Session</span>
                <h6 class="fw-bold text-white mb-1">{{ $nearestModule?->title ?? 'To be announced' }}</h6>
                <span class="small opacity-75">
                    @if($nearestModule?->start_at_utc_iso)
                        <span data-local-datetime="{{ $nearestModule->start_at_utc_iso }}">{{ $nearestModule->start_at->format('M d, Y h:i A') }}</span>
                    @else
                        Schedule will be updated soon
                    @endif
                </span>
            </div>

            <a href="{{ route('modules') }}" class="btn btn-white-custom fw-semibold px-4">View Chapters</a>
        </div>
    </div>
</div>
