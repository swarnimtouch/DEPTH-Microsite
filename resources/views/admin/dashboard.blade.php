@extends('layouts.admin')

@section('content')
    <div class="content d-flex flex-column flex-column-fluid admin-overview" id="kt_content">
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                @endif

                <form class="overview-filters" method="GET" action="{{ route('admin.dashboard') }}">
                    <label>Chapter
                        <select name="module" class="form-select form-select-solid">
                            <option value="">All chapters</option>
                            @foreach($allModules as $module)<option value="{{ $module->id }}" @selected((string) $filters['module'] === (string) $module->id)>{{ $module->title }}</option>@endforeach
                        </select>
                    </label>
                    <label>Country
                        <select name="country" class="form-select form-select-solid">
                            <option value="">All countries</option>
                            @foreach($countries as $country)<option value="{{ $country }}" @selected($filters['country'] === $country)>{{ $country }}</option>@endforeach
                        </select>
                    </label>
                    <label>Period
                        <select name="period" class="form-select form-select-solid">
                            <option value="all" @selected($filters['period'] === 'all')>All time</option>
                            <option value="30" @selected($filters['period'] === '30')>Last 30 days</option>
                            <option value="90" @selected($filters['period'] === '90')>Last 90 days</option>
                            <option value="365" @selected($filters['period'] === '365')>Last 12 months</option>
                        </select>
                    </label>
                    <button type="submit" class="btn btn-primary overview-apply">Apply filters</button>
                    @if($filters['country'] || $filters['module'] || $filters['period'] !== 'all')<a class="overview-reset" href="{{ route('admin.dashboard') }}">Reset</a>@endif
                </form>

                <div class="overview-kpis">
                    <article class="overview-kpi"><span>Total users</span><strong>{{ number_format($stats['participants']) }}</strong><small>{{ number_format($stats['participants']) }} registered participants</small></article>
                    <article class="overview-kpi"><span>Total attendance</span><strong>{{ number_format($stats['attendance']) }}</strong><small>{{ number_format($stats['attendance']) }} participants attended</small></article>
                    <article class="overview-kpi"><span>Completed chapters</span><strong>{{ number_format($stats['completed_chapters']) }}</strong><small>Feedback submitted</small></article>
                    <article class="overview-kpi"><span>Certificates issued</span><strong>{{ number_format($stats['participation_certificates'] + $stats['advanced_certificates']) }}</strong><small>{{ number_format($stats['participation_certificates']) }} participation · {{ number_format($stats['advanced_certificates']) }} advanced</small></article>
                </div>

                <div class="overview-middle">
                    <section class="overview-panel chapter-panel">
                        <h2>Chapter completion</h2>
                        <p>Participants who submitted chapter feedback</p>
                        <div class="chapter-bars">
                            @forelse($chapterProgress as $index => $chapter)
                                <div class="chapter-bar-row"><b>Chapter {{ $index + 1 }} — {{ $chapter['title'] }}</b><div class="chapter-track"><i style="width:{{ $chapter['rate'] }}%"></i></div><strong>{{ $chapter['rate'] }}%</strong></div>
                            @empty
                                <div class="overview-empty">No active chapters found for this selection.</div>
                            @endforelse
                        </div>
                    </section>

                    <section class="overview-panel specialty-panel">
                        <h2>Specialty segmentation</h2>
                        <p>Registered participants by specialty</p>
                        @php $specialtyTotal = (int) $specialityBreakdown->sum('total'); $specialtyScale = max(1, $specialtyTotal); $specialtyColors = ['#e31e2f', '#3263ae', '#192f5c', '#f05440', '#7f8cab', '#a12b41', '#62a6a0', '#cf9145']; $specialtyOffset = 0; @endphp
                        <div class="specialty-content">
                            <div class="specialty-donut" style="background:conic-gradient(@foreach($specialityBreakdown as $index => $speciality) {{ $specialtyColors[$index % count($specialtyColors)] }} {{ $specialtyOffset }}% {{ $specialtyOffset += ((int) $speciality->total / $specialtyScale * 100) }}%{{ $index < $specialityBreakdown->count() - 1 ? ', ' : '' }} @endforeach)"><div>{{ number_format($specialtyTotal) }}<small>users</small></div></div>
                            <ul class="specialty-legend">
                                @forelse($specialityBreakdown as $index => $speciality)
                                    <li><i style="background:{{ $specialtyColors[$index % count($specialtyColors)] }}"></i><span>{{ $speciality->label }}</span><b>{{ number_format($speciality->total) }}</b></li>
                                @empty
                                    <li class="overview-empty">No specialty data yet.</li>
                                @endforelse
                            </ul>
                        </div>
                    </section>
                </div>

                <section class="overview-panel live-question-progress-panel">
                    <div class="live-progress-heading">
                        <div>
                            <h2>Live Poll / Quiz Progress</h2>
                            <p>Question-wise participation and answer distribution</p>
                        </div>
                    </div>
                    <div class="live-progress-grid">
                        @forelse($liveQuestionProgress as $question)
                            <article class="live-progress-card">
                                <div class="live-progress-card-head">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="live-progress-badge">{{ strtoupper($question['type']) }}</span>
                                        <span class="live-progress-status is-{{ $question['status'] }}">{{ ucfirst($question['status']) }}</span>
                                    </div>
                                    <span class="live-progress-response-count">{{ number_format($question['responses']) }} responses</span>
                                </div>
                                <small class="live-progress-module">{{ $question['module'] }}</small>
                                <h3>{{ $question['question'] }}</h3>
                                <div class="live-option-progress">
                                    @foreach($question['options'] as $option)
                                        <div class="live-option-row">
                                            <div class="live-option-copy">
                                                <span>{{ $option['label'] }} @if($option['is_correct'])<em>Correct</em>@endif</span>
                                                <strong>{{ $option['responses'] }} · {{ $option['rate'] }}%</strong>
                                            </div>
                                            <div class="live-option-track"><i style="width:{{ $option['rate'] }}%"></i></div>
                                        </div>
                                    @endforeach
                                </div>
                            </article>
                        @empty
                            <div class="overview-empty live-progress-empty">No poll or quiz questions found for the selected filters.</div>
                        @endforelse
                    </div>
                </section>

                <section class="overview-panel country-panel">
                    <h2>Country breakdown</h2>
                    <p>Registrations by country — darker red means more participants</p>
                    @php $maxCountryCount = max(1, (int) $countryBreakdown->max('total')); @endphp
                    <div class="country-tiles">
                        @forelse($countryBreakdown as $country)
                            @php $intensity = 0.2 + ((int) $country->total / $maxCountryCount) * .8; @endphp
                            <div class="country-tile" style="--tile-alpha:{{ $intensity }}"><strong>{{ number_format($country->total) }}</strong><span>{{ $country->label }}</span></div>
                        @empty
                            <div class="overview-empty">No participant registrations for the selected filters.</div>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .admin-overview{--overview-red:#982a40;--overview-ink:#17243a;--overview-muted:#738094;--overview-rule:#e4ddd4}.overview-filters{display:flex;align-items:end;gap:14px;padding:2px 0 20px;margin-bottom:20px;border-bottom:1px solid var(--overview-rule);flex-wrap:wrap}.overview-filters label{display:grid;gap:5px;min-width:170px;color:var(--overview-muted);font-size:10px;font-weight:700;letter-spacing:.1em;text-transform:uppercase}.overview-filters select{min-width:190px;background-color:#fff!important;border:1px solid #d9d1c7!important;border-radius:0!important;color:var(--overview-ink)!important;font-size:13px!important}.overview-apply{height:40px;background:var(--overview-red)!important;border-color:var(--overview-red)!important}.overview-reset{font-size:12px;padding:10px;color:var(--overview-muted)}.overview-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin-bottom:20px}.overview-kpi,.overview-panel{background:#fff;border:1px solid var(--overview-rule)}.overview-kpi{min-height:142px;border-top:2px solid var(--overview-red);padding:18px 20px;display:flex;flex-direction:column}.overview-kpi>span{color:var(--overview-muted);font-size:10px;font-weight:800;letter-spacing:.12em;text-transform:uppercase}.overview-kpi>strong{margin:9px 0 4px;color:var(--overview-ink);font-size:34px;font-weight:400;line-height:1.1}.overview-kpi>small{color:var(--overview-muted);font-size:11px;line-height:1.4}.overview-kpi>small:before{content:'';display:inline-block;width:7px;height:7px;margin:0 7px 1px 0;border-radius:50%;background:var(--overview-red)}.overview-middle{display:grid;grid-template-columns:minmax(0,1.65fr) minmax(360px,.85fr);gap:16px;margin-bottom:20px}.overview-panel{padding:20px 22px}.overview-panel h2{margin:0;color:var(--overview-ink);font-size:18px;font-weight:700}.overview-panel>p{margin:4px 0 18px;color:var(--overview-muted);font-size:12px}.chapter-bars{display:grid;gap:13px;padding:4px 0 3px}.chapter-bar-row{display:grid;grid-template-columns:minmax(145px,235px) minmax(100px,1fr) 42px;gap:12px;align-items:center}.chapter-bar-row>b{overflow:hidden;color:#465265;font-size:12px;text-overflow:ellipsis;white-space:nowrap}.chapter-track{height:15px;background:#e5ded4}.chapter-track i{display:block;height:100%;background:var(--overview-red)}.chapter-bar-row>strong{text-align:right;color:#344054;font-size:12px}.specialty-content{display:grid;grid-template-columns:155px minmax(0,1fr);align-items:center;gap:16px}.specialty-donut{width:150px;height:150px;border-radius:50%;display:grid;place-items:center;background-color:#e9e3da;position:relative}.specialty-donut:before{content:'';position:absolute;inset:28px;border-radius:50%;background:white}.specialty-donut>div{position:relative;display:grid;justify-items:center;color:var(--overview-ink);font-size:21px;font-weight:700}.specialty-donut small{color:var(--overview-muted);font-size:10px;font-weight:500}.specialty-legend{display:grid;gap:8px;padding:0;margin:0;list-style:none}.specialty-legend li{display:grid;grid-template-columns:10px minmax(0,1fr) auto;gap:8px;align-items:center;color:#465265;font-size:11px}.specialty-legend i{width:10px;height:10px}.specialty-legend b{color:#29364a;font-size:11px}.live-question-progress-panel{margin-bottom:20px}.live-progress-heading{margin-bottom:18px}.live-progress-heading p{margin:4px 0 0;color:var(--overview-muted);font-size:12px}.live-progress-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.live-progress-card{min-width:0;padding:17px 18px;border:1px solid #ebe5dd;background:#fcfbf9}.live-progress-card-head,.live-option-copy{display:flex;align-items:center;justify-content:space-between;gap:12px}.live-progress-badge,.live-progress-status{display:inline-flex;padding:4px 7px;font-size:9px;font-weight:800;letter-spacing:.06em;text-transform:uppercase}.live-progress-badge{background:#eaf4ff;color:#2474a8}.live-progress-status{background:#eceff3;color:#697386}.live-progress-status.is-live{background:#e8f8ef;color:#218653}.live-progress-status.is-stopped{background:#fff0f1;color:#bd2434}.live-progress-response-count{color:#596579;font-size:11px;font-weight:700;white-space:nowrap}.live-progress-module{display:block;margin-top:10px;color:var(--overview-muted);font-size:10px;overflow-wrap:anywhere}.live-progress-card h3{margin:5px 0 15px;color:var(--overview-ink);font-size:14px;line-height:1.45;overflow-wrap:anywhere}.live-option-progress{display:grid;gap:10px;margin-top:15px;padding-top:14px;border-top:1px solid #ebe5dd}.live-option-copy{margin-bottom:5px;color:#4e5a6c;font-size:10px}.live-option-copy span{min-width:0;overflow-wrap:anywhere}.live-option-copy strong{flex:none;color:#344054;font-size:10px}.live-option-copy em{display:inline-flex;margin-left:5px;padding:2px 5px;background:#e8f8ef;color:#218653;font-size:8px;font-style:normal;font-weight:800;text-transform:uppercase}.live-option-track{height:5px;overflow:hidden;background:#e9e5df}.live-option-track i{display:block;height:100%;background:var(--overview-red)}.live-progress-empty{grid-column:1/-1}.country-panel{margin-bottom:22px}.country-tiles{display:grid;grid-template-columns:repeat(auto-fill,minmax(145px,1fr));gap:9px}.country-tile{min-height:74px;padding:12px 14px;display:flex;flex-direction:column;justify-content:center;gap:4px;background:rgba(227,30,47,var(--tile-alpha));color:#fff}.country-tile strong{font-size:22px;line-height:1}.country-tile span{font-size:11px;font-weight:600}.overview-empty{padding:18px 0;color:var(--overview-muted);font-size:12px}@media(max-width:1100px){.overview-middle{grid-template-columns:1fr}.specialty-content{grid-template-columns:150px 1fr}.overview-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:800px){.live-progress-grid{grid-template-columns:1fr}}@media(max-width:575px){.overview-kpis{grid-template-columns:1fr 1fr;gap:9px}.overview-kpi{padding:13px;min-height:125px}.overview-kpi>strong{font-size:28px}.overview-middle{gap:12px}.overview-panel{padding:16px}.chapter-bar-row{grid-template-columns:1fr 36px;gap:6px}.chapter-bar-row>b{grid-column:1/-1}.chapter-track{grid-column:1}.chapter-bar-row>strong{grid-column:2;grid-row:2}.specialty-content{grid-template-columns:1fr}.specialty-donut{margin:auto}.overview-filters{align-items:stretch}.overview-filters label,.overview-filters select{width:100%}}
    </style>
@endpush
