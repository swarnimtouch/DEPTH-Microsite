<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $title }} · DEPTH by Hetero</title>
<meta name="description" content="Recognising &amp; Managing Depression in Primary Care — an expert-led live session with Prof. Siegfried Kasper and four HCP case booklets. A Hetero educational initiative.">
<link rel="icon" href="{{ siteSettingAsset('Favicon', 'web/depth/assets/hetero.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('web/depth/style.css') }}?v={{ filemtime(public_path('web/depth/style.css')) }}">
<style>
.logo-hetero{height:48px}.trust-row.solo .t-hetero{height:44px}.arm-logos .a-hetero{height:34px}
.profile-form-card{width:100%;max-width:1100px;padding:34px 36px}.profile-edit-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:22px 24px}.profile-edit-grid .field{display:grid;gap:8px}.profile-edit-grid .field span{font-size:12px;font-weight:800;color:#4e5668;text-transform:uppercase;letter-spacing:.08em}.profile-edit-grid .field em{color:#e0242b;font-style:normal}.profile-edit-grid .input{width:100%;min-height:52px;border:1px solid #d9dee7;border-radius:9px;background:#fff;padding:12px 16px;color:#1f2a44;font:inherit}.profile-edit-grid .input:focus{outline:2px solid rgba(224,36,43,.14);border-color:#e0242b}.profile-edit-grid .span2{grid-column:1/-1}.profile-form-actions{display:flex;justify-content:flex-end;align-items:center;gap:14px;margin-top:28px}.profile-errors{margin-bottom:20px;padding:14px 16px;border:1px solid #f2b3b7;border-radius:9px;background:#fff2f3;color:#a31319}.profile-errors ul{margin:6px 0 0 18px}
@media(max-width:760px){.logo-hetero{height:36px}.trust-row.solo .t-hetero{height:38px}.arm-logos .a-hetero{height:30px}.profile-form-card{padding:24px 20px}.profile-edit-grid{grid-template-columns:1fr}.profile-edit-grid .span2{grid-column:auto}.profile-form-actions{align-items:stretch;flex-direction:column}}
</style>
</head>
<body data-view="{{ $dashboardSection }}">
<div class="page">

  @include('partials.website.depth_dashboard_header')

  <main>

    <!-- ================================================================ LANDING -->
    @if($dashboardSection === 'landing')
    <section class="view landing" data-view="landing">
      <div class="hero hero-split">
        <div class="hero-in">
          <div class="hero-copy centred">
            <div class="kicker-line"><span class="kicker">Advancing conversations in depression</span></div>
            <h1 class="hero-title">Recognising &amp; Managing Depression <span>in Primary Care</span></h1>
            <p class="hero-it">Understanding hidden presentations, red flags, diagnosis, referral and real-world management</p>
            <p class="hero-sub">An expert-led clinical session combining evidence, case-based insights and practical approaches to recognising, diagnosing and managing depression in primary care.</p>
            <ul class="hero-line"><li>Recognise the signs.</li><li>Close the gaps.</li><li>Transform care.</li></ul>
            <div class="btn-row"><a class="btn btn-grad lg" href="#register" data-scroll="register">Register now →</a><a class="btn btn-line lg" href="#overview" data-go="overview">Explore the program</a></div>
          </div>
          <aside class="hero-form" id="register">
            <div class="reg-card" id="reg-card">
              <div class="reg-head"><h2 class="reg-title">Register for the DEPTH Series</h2><p>Join an expert-led session exploring practical approaches to recognising, assessing and managing depression through evidence and case-based discussion. Free for healthcare professionals.</p></div>
              <form class="reg" id="reg-form" novalidate>
                <fieldset class="events"><legend>Select event <em>*</em></legend>
                  <label class="ev"><input type="radio" name="event" value="1" checked><span><b>Depression in primary care — live session</b><small>20 Nov 2026 (Friday) · 4:00 – 5:00 PM IST · Prof. Siegfried Kasper</small></span></label>
                </fieldset>
                <label class="field span2"><span>Full name <em>*</em></span><input class="input" id="reg-name" required autocomplete="name" placeholder="Dr. Maria Santos"></label>
                <label class="field"><span>Speciality <em>*</em></span><select class="input" id="reg-spec" required></select></label>
                <label class="field"><span>Institution / Hospital <em>*</em></span><input class="input" id="reg-inst" required autocomplete="organization" placeholder="Philippine General Hospital"></label>
                <label class="field"><span>Country <em>*</em></span><select class="input" id="reg-country" required></select></label>
                <label class="field"><span>City <em>*</em></span><select class="input" id="reg-city" required disabled></select></label>
                <label class="field"><span>Email ID <em>*</em></span><input class="input" id="reg-email" type="email" required autocomplete="email" placeholder="maria.santos@hospital.ph"></label>
                <label class="field"><span>Mobile number <small>(optional)</small></span><span class="tel-row"><select class="input isd" id="reg-isd" aria-label="ISD code"></select><input class="input" id="reg-mobile" type="tel" autocomplete="tel" placeholder="917 123 4567"></span></label>
                <label class="consent span2"><input type="checkbox" id="reg-consent" required><span>I consent to receive communication about this program and related educational content from Hetero. <em>*</em></span></label>
                <div class="reg-actions span2"><button type="submit" class="btn btn-grad lg wide">Complete registration →</button><div class="hint">Already registered? <button type="button" class="link" data-show-login>Log in</button></div></div>
              </form>
            </div>
            <div class="reg-card" id="login-card" hidden>
              <div class="reg-head"><h2>Welcome back</h2><p>Log in with your email or mobile number. Open access for now — no password needed.</p></div>
              <form class="reg" id="login-form" novalidate>
                <label class="field span2"><span>Email or mobile <em>*</em></span><input class="input" id="login-id" required placeholder="maria.santos@hospital.ph"></label>
                <div class="reg-actions span2"><button type="submit" class="btn btn-blue lg wide">Log in →</button><div class="hint">New here? <button type="button" class="link" data-show-register>Register</button></div></div>
              </form>
            </div>
          </aside>
        </div>
        <div class="hero-below">
          <div class="badge-card">
            <img class="bc-kv" src="{{ asset('web/depth/assets/visual/keyvisual-red.png') }}" alt="">
            <div class="bc-body">
              <span class="live-pill">▶ Case-based live session</span>
              <div class="bc-title">DEPTH</div>
              <div class="bc-sub">Depression Education Program for Therapeutic Excellence</div>
              <div class="bc-tag">by Hetero · Case-based learning → Live session → Post-read</div>
              <div class="bc-dates"><div><small>Live session</small><b>20 Nov 2026 · Friday</b><span>4:00 – 5:00 PM IST</span></div><div><small>Speaker</small><b>Prof. Siegfried Kasper</b><span>Medical University of Vienna, Austria</span></div></div>
            </div>
          </div>
          <div class="countdowns"><div class="countdown" id="land-countdown"></div></div>
        </div>
      </div>
    </section>
    @endif

    <!-- ================================================================ THANK YOU -->
    @if($dashboardSection === 'thanks')
    <section class="view wrap thanks" data-view="thanks" hidden>
      <div class="thanks-grid">
        <div>
          <div class="check">✅</div>
          <h1 class="h1">You're registered!</h1>
          <p class="body">Thank you for registering. Your confirmation and joining details will be shared on your registered email / mobile number.</p>
          <div class="kicker" style="margin-top:26px">Your registration is confirmed for</div>
          <div class="card strip"><table class="table"><thead><tr><th>Session</th><th>Date</th><th>Time</th><th>Topic</th></tr></thead><tbody id="thanks-rows"></tbody></table></div>
          <div class="btn-row" style="margin-top:22px"><button class="btn btn-grad" data-go="overview">Explore the program →</button><button class="btn btn-line" data-ics="1">Add to calendar</button></div>
        </div>
        <aside class="thanks-aside">
          <div class="card pad soft"><div class="kicker">Your learning journey</div><ol class="steps"><li><b>Case-based learning</b><span>Work through the four HCP case booklets before the session.</span></li><li><b>Live session</b><span>Join on 20 Nov with one click from the event card.</span></li><li><b>Post-read engagement</b><span>Recording, 1-page post-read, key takeaways and your certificate.</span></li></ol></div>
        </aside>
      </div>
    </section>
    @endif

    @include('website.dashboard.sections.' . $dashboardSection)

  </main>

  <footer class="ftr">
    <svg class="swoosh" viewBox="0 0 1600 90" preserveAspectRatio="none" aria-hidden="true"><path d="M0 70 C 400 10, 900 110, 1600 30" fill="none" stroke="#E0242B" stroke-width="2"/><path d="M0 85 C 450 30, 950 120, 1600 50" fill="none" stroke="#1F2A44" stroke-width="2"/></svg>
    <div class="ftr-in">
      <div class="kicker muted">An educational initiative brought to you by</div>
      <div class="trust-row solo"><img class="t-hetero" src="{{ asset('web/depth/assets/hetero.png') }}" alt="Hetero"></div>
      <div class="arms-row"><div class="arms"><small>Marketing arms</small><div class="arm-logos"><img src="{{ asset('web/depth/assets/partners/hetero.png') }}" alt="Hetero" class="a-hetero"><img src="{{ asset('web/depth/assets/partners/hetero-healthcare.svg') }}" alt="Hetero Healthcare" class="a-hh"><img src="{{ asset('web/depth/assets/partners/amarox.png') }}" alt="Amarox" class="a-amarox"><img src="{{ asset('web/depth/assets/partners/camber.png') }}" alt="Camber Pharmaceuticals" class="a-camber"><img src="{{ asset('web/depth/assets/partners/seven-pharma.png') }}" alt="Seven Pharma" class="a-seven"></div></div><div class="arms tech"><small>Technical support by</small><img src="{{ asset('web/depth/assets/alphamed.png') }}" alt="AlphaMed"></div></div>
      <p class="t-fac">Faculty: {{ $depthSpeakers->pluck('name')->filter()->implode(', ') ?: ($nearestModule?->speaker_name ?: 'To be announced') }}</p>
      <p class="tagline">Knowledge drives a brighter tomorrow</p>
      <p class="disclaimer">The content is intended for <b>Healthcare Professionals (HCPs) only</b> and is provided for educational and scientific purposes. The information presented is based on available scientific evidence and expert perspectives and is not intended to replace professional medical judgment, clinical guidelines, or individual patient assessment. Prescribing decisions should be made based on the individual patient's clinical condition, applicable prescribing information, and local regulations. Product information and recommendations may vary by country.</p>
      <div class="ftr-meta"><span>© 2026 Hetero Healthcare Ltd. DEPTH — Depression Education Program for Therapeutic Excellence.</span><span>Technical support by AlphaMed</span></div>
    </div>
  </footer>
</div>

<div class="sticky-cta" id="sticky-cta" hidden></div>
<div class="modal" id="modal" hidden><div class="modal-card"><button class="modal-x" data-close aria-label="Close">×</button><div class="modal-video" id="modal-art"><div class="play"><span></span></div></div><h3 id="modal-title"></h3><p id="modal-body"></p></div></div>
<div class="toast" id="toast" hidden></div>
<script>
window.DEPTH_LOGOUT_URL = @json(route('logout'));
window.DEPTH_INITIAL_VIEW = @json($dashboardSection);
window.DEPTH_ROUTES = @json($dashboardRoutes);
window.DEPTH_FLASH_SUCCESS = @json(session('success'));
window.DEPTH_AGENDA = @json($depthAgenda);
window.DEPTH_SPEAKERS = @json($depthSpeakers);
window.DEPTH_EVENT = @json($depthEvent);
window.DEPTH_RESOURCES = @json($depthResources);
localStorage.setItem('depth.user', JSON.stringify(@json($depthUser)));
</script>
<script src="{{ asset('web/depth/dashboard-static.js') }}?v={{ filemtime(public_path('web/depth/dashboard-static.js')) }}"></script>
</body>
</html>
