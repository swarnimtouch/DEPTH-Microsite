@extends('layouts.website')
@section('page_title', 'DEPTH | Depression Education Program for Therapeutic Excellence')
@section('page_favicon', asset('web/depth/assets/hetero.png'))
@section('standalone_page', 'true')

@section('body')
@php
    $depthAsset = fn (string $file) => asset('web/depth/assets/' . $file);
    $programModule = $modules->first();
    $sessionStart = $programModule?->getRawOriginal('start_at') ? \Carbon\CarbonImmutable::parse($programModule->getRawOriginal('start_at'), 'UTC')->timezone('Asia/Kolkata') : null;
    $sessionEnd = $programModule?->getRawOriginal('end_at') ? \Carbon\CarbonImmutable::parse($programModule->getRawOriginal('end_at'), 'UTC')->timezone('Asia/Kolkata') : null;
    $event = [
        'title' => $programModule?->title ?? 'Recognising & Managing Depression in Primary Care',
        'tagline' => $programModule?->overview ?? 'Understanding hidden presentations, red flags, diagnosis, referral and real-world management',
        'date' => $sessionStart?->format('d F Y') ?? '20 November 2026',
        'date_short' => $sessionStart?->format('d M Y') ?? '20 Nov 2026',
        'day' => $sessionStart?->format('l') ?? 'Friday',
        'time' => ($sessionStart && $sessionEnd) ? $sessionStart->format('g:i A') . ' - ' . $sessionEnd->format('g:i A') . ' IST' : '4:00 PM - 5:00 PM IST',
        'start_iso' => $programModule?->start_at_utc_iso ?? '2026-11-20T10:30:00+00:00',
        'speaker' => $programModule?->speaker_name ?? 'Prof. Siegfried Kasper',
    ];
@endphp

<div class="page depth-page">
    <header class="hdr">
        <div class="hdr-in">
            <a class="brand" href="{{ route('home') }}" aria-label="DEPTH home">
                <img class="logo-hetero" src="{{ $depthAsset('hetero.png') }}" alt="Hetero">
            </a>
            <nav class="hdr-nav" aria-label="Primary navigation">
                <a href="{{ auth('web')->check() ? route('course.dashboard') : '#register' }}">Program</a>
                @auth('web')
                    <a class="btn btn-line sm" href="{{ route('dashboard') }}">Dashboard</a>
                @else
                    <a class="btn btn-grad sm" href="#register">Register now</a>
                @endauth
            </nav>
        </div>
    </header>

    <main>
        <section class="view landing">
            <div class="hero hero-split">
                <div class="hero-in">
                    <div class="hero-copy centred">
                        <div class="kicker-line"><span class="kicker">Advancing conversations in depression</span></div>
                        <h1 class="hero-title">Recognising &amp; Managing Depression <span>in Primary Care</span></h1>
                        <p class="hero-it">{{ $event['tagline'] }}</p>
                        <p class="hero-sub">An expert-led clinical session combining evidence, case-based insights and practical approaches to recognising, diagnosing and managing depression in primary care.</p>
                        <ul class="hero-line"><li>Recognise the signs</li><li>Close the gaps</li><li>Transform care</li></ul>
                        <div class="btn-row"><a class="btn btn-grad lg" href="#register">Register now &rarr;</a><a class="btn btn-line lg" href="{{ auth('web')->check() ? route('course.dashboard') : '#register' }}">Explore the program</a></div>
                    </div>

                    <aside class="hero-form" id="register">
                        <div class="reg-card">
                            <div class="reg-head">
                                <h2>Register for the DEPTH Series</h2>
                                <p>Join an expert-led session exploring practical approaches to recognising, assessing and managing depression through evidence and case-based discussion. Free for healthcare professionals.</p>
                            </div>
                            <form method="POST" action="{{ route('register.store') }}" id="homepageRegistrationForm" class="reg" data-check-email-url="{{ route('check_email') }}" data-check-mobile-url="{{ route('check_mobile') }}" data-csrf-token="{{ csrf_token() }}">
                                @csrf
                                <input type="hidden" name="registration_context" value="landing">
                                <fieldset class="events epic-events span2">
                                    <legend>Select event <em>*</em></legend>
                                    <label class="ev"><input type="radio" name="event" value="{{ $programModule?->id ?? 1 }}" checked><span><b>Depression in primary care - live session</b><small>{{ $event['date_short'] }} ({{ $event['day'] }}) · {{ $event['time'] }} · {{ $event['speaker'] }}</small></span></label>
                                </fieldset>

                                <label class="field span2"><span>Full name <em>*</em></span><input class="input" id="homepage-name" type="text" name="name" value="{{ old('name') }}" autocomplete="name" placeholder="Dr. Maria Santos" required></label>
                                <label class="field"><span>Speciality <em>*</em></span><select class="input" id="homepage-speciality" name="speciality_id" required><option value="">Select speciality</option>@foreach($specialities as $speciality)<option value="{{ $speciality->id }}" @selected((string) old('speciality_id') === (string) $speciality->id)>{{ $speciality->name }}</option>@endforeach</select></label>
                                <label class="field"><span>Institution / Hospital <em>*</em></span><input class="input" id="homepage-hospital" type="text" name="hospital" value="{{ old('hospital') }}" autocomplete="organization" placeholder="Hospital or institution" required></label>
                                <label class="field"><span>Country <em>*</em></span><select class="input" id="homepage-country" name="country" required><option value="">Select country</option>@foreach($countries as $country)<option value="{{ $country->name }}" @selected(old('country') === $country->name)>{{ $country->name }}</option>@endforeach</select></label>
                                <label class="field"><span>City <em>*</em></span><input class="input" id="homepage-city" type="text" name="city" value="{{ old('city') }}" autocomplete="address-level2" placeholder="City" required></label>
                                <label class="field"><span>Email ID <em>*</em></span><input class="input" id="homepage-email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" placeholder="name@hospital.org" required></label>
                                <label class="field"><span>Mobile number <em>*</em></span>
                                    <input type="hidden" name="mobile" id="homepage-mobile-full" value="{{ old('mobile') }}">
                                    <input type="hidden" name="mobile_country_code" id="homepage-mobile-country-code" value="{{ old('mobile_country_code', '91') }}">
                                    <input type="hidden" name="mobile_country_iso" id="homepage-mobile-country-iso" value="{{ old('mobile_country_iso', 'in') }}">
                                    <span class="tel-row micro-mobile-group"><select class="input isd" id="homepage-mobile-country" aria-label="Mobile country code"><option value="{{ old('mobile_country_iso', 'in') }}" data-dial="{{ old('mobile_country_code', '91') }}">+{{ old('mobile_country_code', '91') }}</option></select><input class="input" id="homepage-mobile-number" type="tel" name="mobile_number" value="{{ old('mobile_number') }}" autocomplete="tel-national" inputmode="numeric" placeholder="0000000000" required></span>
                                </label>
                                <label class="consent epic-consent span2"><input type="checkbox" name="consent" value="1" @checked(old('consent')) required><span>I consent to receive communication about this program and related educational content from Hetero. <em>*</em></span></label>
                                <div class="reg-actions span2"><button type="submit" class="btn btn-grad lg wide">Complete registration &rarr;</button><div class="hint">Already registered? <a class="link" href="{{ route('login') }}">Log in</a></div></div>
                            </form>
                        </div>
                    </aside>
                </div>

                <div class="hero-below">
                    <div class="badge-card">
                        <img class="bc-kv" src="{{ $depthAsset('visual/keyvisual-red.png') }}" alt="DEPTH clinical learning visual">
                        <div class="bc-body">
                            <span class="live-pill">Case-based live session</span>
                            <div class="bc-title">DEPTH</div>
                            <div class="bc-sub">Depression Education Program for Therapeutic Excellence</div>
                            <div class="bc-tag">by Hetero · Case-based learning &rarr; Live session &rarr; Post-read</div>
                            <div class="bc-dates"><div><small>Live session</small><b>{{ $event['date_short'] }} · {{ $event['day'] }}</b><span>{{ $event['time'] }}</span></div><div><small>Speaker</small><b>{{ $event['speaker'] }}</b><span>Medical University of Vienna, Austria</span></div></div>
                        </div>
                    </div>
                    <div class="countdown" data-countdown="{{ $event['start_iso'] }}"><strong class="label">Live session opens in</strong><div class="cd"><div><b data-days>00</b><span>Days</span></div><div><b data-hours>00</b><span>Hrs</span></div><div><b data-minutes>00</b><span>Min</span></div><div><b data-seconds>00</b><span>Sec</span></div></div></div>
                </div>
            </div>
        </section>

<section class="program-showcase" id="program">
            <div class="program-shell">
                <div class="program-heading">
                    <div><span class="program-kicker">Live expert-led learning</span><h2>Upcoming live session</h2></div>
                    <p>One focused DEPTH session designed to support practical recognition, diagnosis and management of depression in primary care.</p>
                </div>
                @forelse($modules as $module)
                    @php
                        $moduleStart = $module->getRawOriginal('start_at') ? \Carbon\CarbonImmutable::parse($module->getRawOriginal('start_at'), 'UTC')->timezone('Asia/Kolkata') : null;
                        $moduleEnd = $module->getRawOriginal('end_at') ? \Carbon\CarbonImmutable::parse($module->getRawOriginal('end_at'), 'UTC')->timezone('Asia/Kolkata') : null;
                        $moduleHref = auth('web')->check() ? route('modules.show', $module->slug) : '#register';
                    @endphp
                    <div class="program-table" role="table" aria-label="Upcoming DEPTH session">
                        <div class="program-table-head" role="row"><span role="columnheader">Session</span><span role="columnheader">Date</span><span role="columnheader">Time</span><span role="columnheader">Topic</span></div>
                        <div class="program-table-row" role="row"><strong role="cell">Session {{ $loop->iteration }}</strong><span role="cell">{{ $moduleStart?->format('d F Y') ?? 'Date to be announced' }}</span><span role="cell">{{ $moduleStart && $moduleEnd ? $moduleStart->format('g:i A').' - '.$moduleEnd->format('g:i A').' IST' : 'Time to be announced' }}</span><span role="cell">{{ $module->title }}</span></div>
                    </div>
                    <article class="program-session-card">
                        <div class="program-session-art"><img src="{{ $depthAsset('visual/keyvisual-red.png') }}" alt="DEPTH depression education visual"><span>Session {{ $loop->iteration }}</span></div>
                        <div class="program-session-body">
                            <div class="program-session-meta"><strong>{{ $moduleStart?->format('d F Y') ?? 'Date to be announced' }}</strong><span>{{ $moduleStart && $moduleEnd ? $moduleStart->format('g:i A').' - '.$moduleEnd->format('g:i A').' IST' : 'Time to be announced' }}</span></div>
                            <h3>{{ $module->title }}</h3><p>{{ $module->overview }}</p>
                            <div class="program-speaker"><small>Speaker</small><strong>{{ $module->speaker_name ?: 'To be announced' }}</strong><span>{{ collect([$module->speaker_designation, $module->speaker_hospital])->filter()->join(' · ') }}</span></div>
                            <a class="program-session-link" href="{{ $moduleHref }}">{{ auth('web')->check() ? 'Open session' : 'Register for session' }} <span aria-hidden="true">&rarr;</span></a>
                        </div>
                    </article>
                @empty
                    <div class="program-empty">Session details will be announced shortly.</div>
                @endforelse
            </div>
        </section>
    </main>

    <footer class="ftr">
        <svg class="swoosh" viewBox="0 0 1600 90" preserveAspectRatio="none" aria-hidden="true"><path d="M0 70 C 400 10, 900 110, 1600 30" fill="none" stroke="#E0242B" stroke-width="2"/><path d="M0 85 C 450 30, 950 120, 1600 50" fill="none" stroke="#1F2A44" stroke-width="2"/></svg>
        <div class="ftr-in">
            <div class="kicker muted">An educational initiative brought to you by</div>
            <div class="trust-row solo"><img class="t-hetero" src="{{ $depthAsset('hetero.png') }}" alt="Hetero"></div>
            <div class="arms-row">
                <div class="arms"><small>Marketing arms</small><div class="arm-logos"><img src="{{ $depthAsset('partners/hetero.png') }}" alt="Hetero" class="a-hetero"><img src="{{ $depthAsset('partners/hetero-healthcare.svg') }}" alt="Hetero Healthcare" class="a-hh"><img src="{{ $depthAsset('partners/amarox.png') }}" alt="Amarox" class="a-amarox"><img src="{{ $depthAsset('partners/camber.png') }}" alt="Camber Pharmaceuticals" class="a-camber"><img src="{{ $depthAsset('partners/seven-pharma.png') }}" alt="Seven Pharma" class="a-seven"></div></div>
                <div class="arms tech"><small>Technical support by</small><img src="{{ $depthAsset('alphamed.png') }}" alt="AlphaMed"></div>
            </div>
            <p class="t-fac">Faculty: Prof. Siegfried Kasper, Medical University of Vienna</p>
            <p class="tagline">Knowledge drives a brighter tomorrow</p>
            <p class="disclaimer">The content is intended for <b>Healthcare Professionals (HCPs) only</b> and is provided for educational and scientific purposes. The information presented is based on available scientific evidence and expert perspectives and is not intended to replace professional medical judgment, clinical guidelines, or individual patient assessment. Prescribing decisions should be made based on the individual patient's clinical condition, applicable prescribing information, and local regulations. Product information and recommendations may vary by country.</p>
            <div class="ftr-meta"><span>&copy; {{ date('Y') }} Hetero Healthcare Ltd. DEPTH - Depression Education Program for Therapeutic Excellence.</span><span>Technical support by AlphaMed</span></div>
        </div>
    </footer>
</div>
@endsection

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('web/depth/style.css') }}?v={{ filemtime(public_path('web/depth/style.css')) }}">
<style>
.depth-page .logo-hetero{height:48px;width:auto}.depth-page .trust-row.solo .t-hetero{height:44px}.depth-page .arm-logos .a-hetero{height:34px}.depth-page .micro-error{display:block;color:#b8141b;font-size:12px;margin-top:5px}.depth-page .input.is-invalid{border-color:#b8141b}.depth-page #register{scroll-margin-top:100px}
@media(max-width:760px){.depth-page .hdr-in{flex-wrap:nowrap}.depth-page .logo-hetero{height:36px}.depth-page .trust-row.solo .t-hetero{height:38px}.depth-page .arm-logos .a-hetero{height:30px}.depth-page .hdr-nav>a:not(.btn){display:none}}
</style>
@endpush

@push('scripts')
<script>window.module={exports:{}};window.exports=window.module.exports;</script>
<script src="https://cdn.jsdelivr.net/npm/country-telephone-data@0.6.3/country_telephone_data.js"></script>
<script>window.countryTelData=window.countryTelData||window.module.exports||{};delete window.module;delete window.exports;</script>
<script src="{{ asset('web/js/ean-microsite.js') }}?v={{ filemtime(public_path('web/js/ean-microsite.js')) }}"></script>
@endpush