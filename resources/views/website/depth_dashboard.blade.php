<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $title }} · DEPTH by Hetero</title>
<meta name="description" content="Recognising &amp; Managing Depression in Primary Care — an expert-led live session with Prof. Siegfried Kasper and four case booklets. A Hetero educational initiative.">
<meta name="csrf-token" content="{{ csrf_token() }}">
@include('partials.reverb_meta')
<link rel="icon" href="{{ siteSettingAsset('Favicon', 'web/depth/assets/hetero.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('web/depth/style.css') }}?v={{ filemtime(public_path('web/depth/style.css')) }}">
<style>
.logo-hetero{height:48px}.brand-divider{width:1px;height:46px;background:#d9dee7}.logo-depth-brand{height:48px;width:auto;max-width:250px}.trust-row.solo .t-hetero{height:44px}.arm-logos .a-hetero{height:34px}
.profile-form-card{width:100%;max-width:1100px;padding:0;overflow:hidden}.profile-form-head{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:28px 36px;border-bottom:1px solid #d9dee7;background:#f7f9fc}.profile-form-head .kicker{margin-bottom:8px}.profile-form-card form,.profile-errors{margin-left:36px;margin-right:36px}.profile-form-card form{padding:30px 0 34px}.profile-edit-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:22px 24px}.profile-edit-grid .field{display:grid;gap:8px}.profile-edit-grid .field span{font-size:12px;font-weight:800;color:#4e5668;text-transform:uppercase;letter-spacing:.08em}.profile-edit-grid .field em{color:#e0242b;font-style:normal}.profile-edit-grid .input{width:100%;min-height:52px;border:1px solid #d9dee7;border-radius:9px;background:#fff;padding:12px 16px;color:#1f2a44;font:inherit}.profile-edit-grid .input:focus{outline:2px solid rgba(224,36,43,.14);border-color:#e0242b}.profile-edit-grid .span2{grid-column:1/-1}.profile-form-actions{display:flex;justify-content:flex-end;align-items:center;gap:14px;margin-top:28px}.profile-errors{margin-top:24px;margin-bottom:0;padding:14px 16px;border:1px solid #f2b3b7;border-radius:9px;background:#fff2f3;color:#a31319}.profile-errors ul{margin:6px 0 0 18px}
.depth-pre-survey-overlay{position:fixed;inset:0;z-index:10000;display:flex;align-items:center;justify-content:center;padding:20px;background:rgba(17,27,49,.72);backdrop-filter:blur(5px)}.depth-pre-survey-dialog{width:min(760px,100%);max-height:calc(100vh - 40px);overflow:auto;border-radius:18px;background:#fff;box-shadow:0 28px 90px rgba(0,0,0,.3)}.depth-pre-survey-head{padding:28px 32px 16px}.depth-pre-survey-kicker{color:#c4161c;font-size:11px;font-weight:800;letter-spacing:.13em;text-transform:uppercase}.depth-pre-survey-head h2{margin:7px 0 8px;font-size:27px;color:#1f2a44}.depth-pre-survey-head p{margin:0;color:#687083;line-height:1.55}.depth-pre-survey-session{margin-top:13px;font-size:13px;font-weight:700;color:#303a50}.depth-pre-survey-body{padding:10px 32px 32px}.depth-pre-survey-progress{height:5px;margin:3px 0 24px;overflow:hidden;border-radius:8px;background:#e8ebf0}.depth-pre-survey-progress span{display:block;height:100%;background:#c4161c}.depth-pre-survey-count{margin-bottom:8px;color:#c4161c;font-size:12px;font-weight:800}.depth-pre-survey-question{margin:0 0 17px;font-size:20px;line-height:1.45;color:#1f2a44}.depth-pre-survey-options{display:grid;gap:10px}.depth-pre-survey-option{display:flex;align-items:center;gap:12px;width:100%;padding:14px 16px;border:1px solid #dce2ea;border-radius:10px;background:#fff;color:#1f2a44;text-align:left;font:inherit;cursor:pointer}.depth-pre-survey-option.is-selected{border-color:#c4161c;background:#fff7f8}.depth-pre-survey-dot{width:18px;height:18px;border:1px solid #bec6d2;border-radius:50%}.depth-pre-survey-option.is-selected .depth-pre-survey-dot{border:5px solid #c4161c}.depth-pre-survey-matrix-wrap{overflow-x:auto;border:1px solid #dce2ea;border-radius:10px}.depth-pre-survey-matrix{width:100%;min-width:640px;border-collapse:collapse}.depth-pre-survey-matrix th,.depth-pre-survey-matrix td{padding:12px;border-right:1px solid #e5e8ee;border-bottom:1px solid #e5e8ee;text-align:center}.depth-pre-survey-matrix th:first-child{text-align:left}.depth-pre-survey-matrix thead th{background:#f5f7fa;font-size:11px}.depth-pre-survey-matrix button{width:28px;height:28px;border:1px solid #bdc6d2;border-radius:50%;background:#fff;cursor:pointer}.depth-pre-survey-matrix button.is-selected{border:8px solid #c4161c}.depth-pre-survey-error{margin-top:16px;padding:11px 13px;border-radius:8px;background:#fff0f1;color:#a31319;font-size:13px}.depth-pre-survey-actions{display:flex;justify-content:space-between;gap:12px;margin-top:24px}.depth-pre-survey-actions button{padding:12px 20px;border-radius:8px;font-weight:700;cursor:pointer}.depth-pre-survey-back{border:1px solid #dce2ea;background:#fff;color:#303a50}.depth-pre-survey-next{border:0;background:linear-gradient(135deg,#d71920,#a70e14);color:#fff}.depth-pre-survey-next:disabled{opacity:.55;cursor:not-allowed}
@media(max-width:760px){.brand{flex:1 1 auto;min-width:0;gap:9px}.logo-hetero{height:36px}.brand-divider{height:34px}.logo-depth-brand{height:34px;max-width:min(36vw,150px)}.trust-row.solo .t-hetero{height:38px}.arm-logos .a-hetero{height:30px}.profile-form-head{align-items:flex-start;padding:22px 20px;flex-direction:column}.profile-form-card form{padding:24px 0}.profile-form-card form,.profile-errors{margin-left:20px;margin-right:20px}.profile-edit-grid{grid-template-columns:1fr}.profile-edit-grid .span2{grid-column:auto}.profile-form-actions{align-items:stretch;flex-direction:column}}
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
                <label class="consent span2"><input type="checkbox" id="reg-webinar-consent" required><span>By attending this webinar, you consent to the collection, use, and processing of your personal data in accordance with Hetero&rsquo;s Privacy Policy. You also agree to the recording of this webinar and grant Hetero the right to store, use, and share the content for educational, promotional, and operational objectives. <em>*</em></span></label>
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
              <img class="bc-depth-logo" src="{{ asset('web/depth/assets/depth-logo.png') }}" alt="DEPTH — Depression Education Program for Therapeutic Excellence">
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
          <div class="card pad soft"><div class="kicker">Your learning journey</div><ol class="steps"><li><b>Case-based learning</b><span>Work through the four case booklets before the session.</span></li><li><b>Live session</b><span>Join on 20 Nov with one click from the event card.</span></li><li><b>Post-read engagement</b><span>Recording, 1-page post-read, key takeaways and your certificate.</span></li></ol></div>
        </aside>
      </div>
    </section>
    @endif

    @include('website.dashboard.sections.' . $dashboardSection)

  </main>

  @include('partials.website.depth_dashboard_footer')
</div>

<div class="sticky-cta" id="sticky-cta" hidden></div>
@if($depthPreSurvey)
<div class="depth-pre-survey-overlay" id="depthPreSurveyModal" role="dialog" aria-modal="true" aria-labelledby="depthPreSurveyTitle">
  <section class="depth-pre-survey-dialog">
    <header class="depth-pre-survey-head"><div class="depth-pre-survey-kicker">Before the live session</div><h2 id="depthPreSurveyTitle">Pre-event survey</h2><p>Please complete this short survey before entering the live room. You will only be asked once.</p><div class="depth-pre-survey-session">{{ $depthPreSurvey['module_title'] }}</div></header>
    <div class="depth-pre-survey-body"><div class="depth-pre-survey-count" id="depthPreSurveyCount"></div><div class="depth-pre-survey-progress"><span id="depthPreSurveyProgress"></span></div><h3 class="depth-pre-survey-question" id="depthPreSurveyQuestion"></h3><div id="depthPreSurveyOptions"></div><div class="depth-pre-survey-error" id="depthPreSurveyError" role="alert" hidden></div><div class="depth-pre-survey-actions"><button type="button" class="depth-pre-survey-back" id="depthPreSurveyBack">Previous</button><button type="button" class="depth-pre-survey-next" id="depthPreSurveyNext">Next</button></div></div>
  </section>
</div>
@endif
<div class="modal" id="modal" hidden><div class="modal-card"><button class="modal-x" data-close aria-label="Close">×</button><div class="modal-video" id="modal-art"><div class="play"><span></span></div></div><h3 id="modal-title"></h3><p id="modal-body"></p></div></div>
<div class="toast" id="toast" hidden></div>
<script>
window.DEPTH_LOGOUT_URL = @json(route('logout'));
window.DEPTH_ASSET_BASE = @json((request()->getBaseUrl() ?: '') . '/web/depth');
window.DEPTH_INITIAL_VIEW = @json($dashboardSection);
window.DEPTH_ROUTES = @json($dashboardRoutes);
window.DEPTH_FLASH_SUCCESS = @json(session('success'));
window.DEPTH_AGENDA = @json($depthAgenda);
window.DEPTH_SPEAKERS = @json($depthSpeakers);
window.DEPTH_EVENT = @json($depthEvent);
window.DEPTH_LIVE_QUESTION_ANSWER_URL = @json(route('live_questions.answer', ['liveQuestion' => '__QUESTION__']));
window.DEPTH_RESOURCES = @json($depthResources);
window.DEPTH_PRE_SURVEY = @json($depthPreSurvey);
localStorage.setItem('depth.user', JSON.stringify(@json($depthUser)));
</script>
<script src="{{ asset('web/depth/dashboard-static.js') }}?v={{ filemtime(public_path('web/depth/dashboard-static.js')) }}"></script>
@if($depthPreSurvey)
<script>
(() => {
  const survey = window.DEPTH_PRE_SURVEY, modal = document.getElementById('depthPreSurveyModal'), options = document.getElementById('depthPreSurveyOptions'), next = document.getElementById('depthPreSurveyNext'), back = document.getElementById('depthPreSurveyBack'), error = document.getElementById('depthPreSurveyError');
  let index = 0; const answers = {};
  const escapeHtml = value => String(value ?? '').replace(/[&<>'"]/g, character => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[character]));
  const complete = question => question.type === 'matrix_single_choice' ? question.rows.every(row => answers[question.id]?.[row.id]) : Boolean(answers[question.id]);
  function render() {
    const question = survey.questions[index];
    document.getElementById('depthPreSurveyCount').textContent = `Question ${index + 1} of ${survey.questions.length}`;
    document.getElementById('depthPreSurveyProgress').style.width = `${((index + 1) / survey.questions.length) * 100}%`;
    document.getElementById('depthPreSurveyQuestion').textContent = question.question;
    back.hidden = index === 0; next.textContent = index === survey.questions.length - 1 ? 'Submit and continue →' : 'Next →'; next.disabled = !complete(question); error.hidden = true;
    if (question.type === 'matrix_single_choice') {
      const headings = question.options.map(option => `<th>${escapeHtml(option.text)}</th>`).join('');
      const rows = question.rows.map(row => `<tr><th>${escapeHtml(row.text)}</th>${question.options.map(option => `<td><button type="button" class="${answers[question.id]?.[row.id] == option.id ? 'is-selected' : ''}" data-row="${row.id}" data-option="${option.id}" aria-label="${escapeHtml(row.text)}: ${escapeHtml(option.text)}"></button></td>`).join('')}</tr>`).join('');
      options.innerHTML = `<div class="depth-pre-survey-matrix-wrap"><table class="depth-pre-survey-matrix"><thead><tr><th>Item</th>${headings}</tr></thead><tbody>${rows}</tbody></table></div>`;
    } else options.innerHTML = `<div class="depth-pre-survey-options">${question.options.map(option => `<button type="button" class="depth-pre-survey-option ${answers[question.id] == option.id ? 'is-selected' : ''}" data-option="${option.id}"><span class="depth-pre-survey-dot"></span><span>${escapeHtml(option.text)}</span></button>`).join('')}</div>`;
  }
  options.addEventListener('click', event => { const choice = event.target.closest('[data-option]'); if (!choice) return; const question = survey.questions[index]; if (question.type === 'matrix_single_choice') { answers[question.id] ||= {}; answers[question.id][choice.dataset.row] = Number(choice.dataset.option); } else answers[question.id] = Number(choice.dataset.option); render(); });
  back.addEventListener('click', () => { if (index > 0) { index--; render(); } });
  next.addEventListener('click', async () => {
    if (index < survey.questions.length - 1) { index++; render(); return; }
    next.disabled = true; next.textContent = 'Saving...'; error.hidden = true;
    try {
      const response = await fetch(survey.submit_url, {method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:JSON.stringify({answers})});
      if (!response.ok) throw new Error((await response.json().catch(() => ({}))).message || 'Unable to save the survey.');
      modal.remove();
    } catch (exception) { error.textContent = exception.message; error.hidden = false; next.disabled = false; next.textContent = 'Submit and continue →'; }
  });
  render();
})();
</script>
@endif
@vite('resources/js/app.js')
</body>
</html>
