/* DEPTH — Depression Education Program for Therapeutic Excellence by Hetero.
   Laravel-routed member pages with shared dynamic rendering and date-driven live states. */
(function () {
  'use strict';

  const ASSET_BASE = String(window.DEPTH_ASSET_BASE || '/web/depth').replace(/\/+$/, '');
  const depthAsset = (path) => `${ASSET_BASE}/${String(path).replace(/^\/+/, '')}`;

  /* ============================================================== DATA */
  const EVENT = {
    n: 1,
    title: 'Depression in Primary Care',
    full: 'Recognising & Managing Depression in Primary Care',
    tagline: 'Understanding hidden presentations, red flags, diagnosis, referral and real-world management',
    desc: 'Gain practical, expert-led insights into recognising red flags, navigating diagnosis and referral, and addressing real-world challenges in depression management.',
    proposition: 'When should primary care clinicians suspect depression — and what should they do next?',
    date: '20 November 2026', dateShort: '20 Nov 2026', day: 'Friday', time: '4:00 – 5:00 PM IST',
    start: '2026-11-20T16:00:00+05:30', end: '2026-11-20T17:00:00+05:30',
    img: depthAsset('assets/visual/brain-red.jpg'), speaker: 'kasper',
    agenda: [
      [5, 'Welcome and Introduction of International speaker', 'Medical Team, Hetero'],
      [30, 'The Hidden Burden: Recognizing Depression in Patients with Cardiometabolic and Gastrointestinal Disorders', 'Prof. Siegfried Kasper'],
      [5, 'Depression in the Busy Clinic: From Red Flags to Diagnosis and Referral', 'Prof. Siegfried Kasper'],
      [15, 'Introduction of Panel', 'Medical Team, Hetero India'],
      [15, 'Panel discussion: Real-world challenges in managing depression in primary care', 'Prof. Siegfried Kasper & Panel Members'],
      [5, 'Q & A Session', 'All Participants'],
      [5, 'Vote of Thanks', 'Medical Team, Hetero India'],
    ],
    synopsis: ['Key clinical messages', 'Case-based learning points', 'Practical considerations'],
    takeaways: [
      ['evidence', 'Depression is common and under-recognised in primary care, especially when it presents through somatic complaints.'],
      ['assessment', 'A structured screen (PHQ-9) plus a safety assessment turns a suspicion into a confident, monitorable diagnosis.'],
      ['treatment', 'Match the antidepressant to the symptom cluster and comorbidity profile; review early for response and tolerability.'],
      ['case', 'In diabetes and cardiovascular disease, treating depression improves adherence and outcomes for the physical condition too.'],
      ['practice', 'Know when to refer: red flags, treatment resistance and safety concerns need a collaborative-care pathway.'],
    ],
  };
  if (window.DEPTH_EVENT && typeof window.DEPTH_EVENT === 'object') {
    Object.entries(window.DEPTH_EVENT).forEach(([key, value]) => {
      if (value !== null && value !== '') EVENT[key] = value;
    });
  }
  if (Array.isArray(window.DEPTH_AGENDA) && window.DEPTH_AGENDA.length) {
    EVENT.agenda = window.DEPTH_AGENDA;
  }
  const RESOURCES = Array.isArray(window.DEPTH_RESOURCES) ? window.DEPTH_RESOURCES : [];

  let CASES = [
    { n: 1, title: 'The patient hiding in plain sight', sub: 'Recognizing depression masked by somatic complaints in your daily OPD', time: '8 min', img: depthAsset('assets/visual/case1-ward.jpg'), tag: 'Recognition', color: '#E0242B' },
    { n: 2, title: 'Diagnosing with confidence', sub: 'Using PHQ-9 as your primary diagnosis and monitoring tool — a step-by-step guide', time: '8 min', img: depthAsset('assets/visual/case2-phq9.jpg'), tag: 'Diagnosis', color: '#1F2A44' },
    { n: 3, title: "The diabetic who won't comply", sub: 'Depression in diabetes and cardiovascular disease — why treating one changes the other', time: '8 min', img: depthAsset('assets/visual/case3-patient.jpg'), tag: 'Comorbidity', color: '#B8141B' },
    { n: 4, title: 'Choosing the right antidepressant for your patient', sub: 'A practical pharmacotherapy guide — matching the molecule to the symptom cluster', time: '8 min', img: depthAsset('assets/visual/case4-pharma.jpg'), tag: 'Pharmacotherapy', color: '#2E5E9E' },
  ];
  const resourceCases = RESOURCES.map((resource, resourceIndex) => ({ resource, resourceIndex }))
    .filter(({ resource }) => resource.category === 'case_book');
  if (resourceCases.length) {
    CASES = resourceCases.map(({ resource, resourceIndex }, index) => ({
      n: index + 1,
      title: resource.label,
      sub: resource.description || (resource.isVideo ? 'Video case-based learning resource' : 'Case-based learning resource'),
      time: resource.isVideo ? 'Video' : resource.type,
      img: resource.thumbnail || depthAsset(`assets/visual/case${(index % 4) + 1}-${['ward', 'phq9', 'patient', 'pharma'][index % 4]}.jpg`),
      tag: 'Case booklet',
      color: ['#E0242B', '#1F2A44', '#B8141B', '#2E5E9E'][index % 4],
      resourceIndex,
      available: resource.available,
      availableAt: resource.availableAt,
    }));
  }

  const SPEAKERS = {
    kasper: {
      name: 'Prof. Siegfried Kasper', photo: depthAsset('assets/speakers/kasper.jpg'), role: 'Speaker',
      designation: 'Professor Emeritus of Psychiatry', designation2: 'Medical University of Vienna, Vienna, Austria', place: 'Vienna, Austria',
      bio: 'Professor Emeritus of Psychiatry and former Head of the Department of Psychiatry and Psychotherapy at the Medical University of Vienna, Austria. A specialist in psychiatry and neurology, his research focuses on depression, neuropsychopharmacology, neurobiology and translational psychiatry. He has authored more than <b>800 PubMed-indexed publications</b> and has held leadership roles in several international psychiatric organisations, including serving as President of the <b>World Federation of Societies of Biological Psychiatry (WFSBP)</b>.',
      expertise: ['Depression & Mood Disorders', 'Neuropsychopharmacology', 'Biological & Translational Psychiatry'],
      profile: [['Current position', 'Professor Emeritus of Psychiatry, Medical University of Vienna'], ['Country / City', 'Vienna, Austria'], ['Professional profile', 'Prof. Siegfried Kasper is Professor Emeritus of Psychiatry at the Medical University of Vienna, Austria. He is an internationally recognised expert in psychiatry, with extensive contributions to depression, neuropsychopharmacology and biological psychiatry. He has authored extensively in the field and has held several leadership positions in international psychiatric organisations.'], ['Clinical / research expertise', 'Depression & Mood Disorders • Neuropsychopharmacology • Biological Psychiatry • Psychopharmacology • Translational Psychiatry'], ['Role in webinar', 'Speaker'], ['Session', 'Depression in Primary Care']],
    },
  };
  if (Array.isArray(window.DEPTH_SPEAKERS) && window.DEPTH_SPEAKERS.length) {
    Object.keys(SPEAKERS).forEach((key) => delete SPEAKERS[key]);
    window.DEPTH_SPEAKERS.forEach((speaker, index) => {
      SPEAKERS[`speaker${index + 1}`] = speaker;
    });
    EVENT.speaker = 'speaker1';
  }

  const TOPICS = [
    ['Clinical evidence and contemporary perspectives on depression in primary care', 'evidence', '#E0242B'],
    ['Practical approaches to recognising depression amid common physical and psychological presentations', 'recognise', '#1F2A44'],
    ['Red flags, screening and clinical assessment to support timely identification', 'flag', '#B8141B'],
    ['Differential diagnosis and safety assessment in patients with overlapping medical symptoms', 'assessment', '#E0242B'],
    ['Treatment, referral and collaborative-care considerations for everyday clinical practice', 'treatment', '#1F2A44'],
    ['Four HCP case booklets featuring practical patient scenarios and clinical decision-making', 'case', '#B8141B'],
    ['Post-read materials to reinforce key messages and support continued learning', 'postread', '#2E5E9E'],
  ];

  const ISD = [['Philippines','+63','PH'],['Thailand','+66','TH'],['Indonesia','+62','ID'],['Malaysia','+60','MY'],['Singapore','+65','SG'],['Vietnam','+84','VN'],['Myanmar','+95','MM'],['Cambodia','+855','KH'],['Laos','+856','LA'],['Brunei','+673','BN'],['Timor-Leste','+670','TL'],['India','+91','IN'],['Other','+','··']];
  const SPECIALITIES = ['General practice / Family medicine', 'Internal medicine', 'Psychiatry', 'Cardiology', 'Endocrinology / Diabetology', 'Gastroenterology', 'Neurology', 'Other'];
  /* South-East Asia: country → major cities */
  const SEA = {
    'Philippines': ['Manila', 'Quezon City', 'Cebu City', 'Davao City', 'Makati', 'Iloilo City', 'Baguio', 'Other'],
    'Thailand': ['Bangkok', 'Chiang Mai', 'Khon Kaen', 'Hat Yai', 'Pattaya', 'Nakhon Ratchasima', 'Other'],
    'Indonesia': ['Jakarta', 'Surabaya', 'Bandung', 'Medan', 'Semarang', 'Makassar', 'Yogyakarta', 'Denpasar', 'Other'],
    'Malaysia': ['Kuala Lumpur', 'Petaling Jaya', 'Penang (George Town)', 'Johor Bahru', 'Ipoh', 'Kota Kinabalu', 'Kuching', 'Other'],
    'Singapore': ['Singapore'],
    'Vietnam': ['Ho Chi Minh City', 'Hanoi', 'Da Nang', 'Hai Phong', 'Can Tho', 'Other'],
    'Myanmar': ['Yangon', 'Mandalay', 'Naypyidaw', 'Other'],
    'Cambodia': ['Phnom Penh', 'Siem Reap', 'Other'],
    'Laos': ['Vientiane', 'Luang Prabang', 'Other'],
    'Brunei': ['Bandar Seri Begawan', 'Other'],
    'Timor-Leste': ['Dili', 'Other'],
  };

  const ICONS = {
    evidence: '<path d="M4 4h11a3 3 0 0 1 3 3v13H7a3 3 0 0 0-3 3z"/><path d="M8 9h6M8 13h6"/>',
    recognise: '<circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/><path d="M8 11h6M11 8v6"/>',
    flag: '<path d="M5 21V4"/><path d="M5 4h12l-2 4 2 4H5"/>',
    assessment: '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/>',
    treatment: '<path d="M10.5 3.5l10 10-7 7-10-10z"/><path d="M6 14l8-8"/>',
    case: '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
    postread: '<path d="M4 19V5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v14"/><path d="M4 19a2 2 0 0 0 2 2h14"/><path d="M9 7h6"/>',
    practice: '<path d="M9 11l2 2 4-4"/><rect x="4" y="4" width="16" height="16" rx="2"/>',
    play: '<path d="M8 5l12 7-12 7z"/>',
    pdf: '<path d="M6 2h8l6 6v14H6z"/><path d="M14 2v6h6"/><path d="M9 14h6M9 18h6"/>',
    calendar: '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
    clock: '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
    video: '<rect x="3" y="6" width="13" height="12" rx="2"/><path d="M16 10l5-3v10l-5-3z"/>',
    cert: '<circle cx="12" cy="9" r="5"/><path d="M9 13l-2 8 5-3 5 3-2-8"/>',
    pearl: '<circle cx="12" cy="12" r="4"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M5 5l2 2M17 17l2 2M5 19l2-2M17 7l2-2"/>',
    refs: '<path d="M4 4h16v16H4z"/><path d="M8 8h8M8 12h8M8 16h4"/>',
  };
  const ico = (k) => `<svg viewBox="0 0 24 24" aria-hidden="true">${ICONS[k] || ICONS.evidence}</svg>`;

  const TABS = [['overview', 'Program overview'], ['agenda', 'Agenda'], ['cases', 'Case booklets'], ['speakers', 'Speaker'], ['live', 'Live session'], ['hub', 'Post-event hub'], ['profile', 'Profile']];
  const GATED = ['overview', 'agenda', 'cases', 'speakers', 'live', 'hub', 'takeaways', 'certificate', 'profile', 'thanks'];
  const ROUTES = window.DEPTH_ROUTES || {};

  /* ============================================================== STATE */
  const $ = (id) => document.getElementById(id);
  const esc = (s) => String(s).replace(/[&<>"]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
  const load = (k, d) => { try { return JSON.parse(localStorage.getItem(k)) ?? d; } catch (e) { return d; } };
  const save = (k, v) => { try { localStorage.setItem(k, JSON.stringify(v)); } catch (e) {} };
  const state = { user: load('depth.user', null), view: window.DEPTH_INITIAL_VIEW || 'overview' };
  const drName = () => { const n = state.user?.name?.trim() || ''; return /^(dr|prof)/i.test(n) ? n : 'Dr. ' + n; };

  function status() {
    const now = Date.now(), s = Date.parse(EVENT.start), e = Date.parse(EVENT.end);
    if (now >= s - 10 * 60000 && now <= e) return 'live';
    if (now > e) return 'replay';
    return 'upcoming';
  }
  const pill = () => { const s = status(); return `<span class="status s-${s}">${s === 'live' ? 'Live now' : s === 'replay' ? 'Recording available' : 'Upcoming'}</span>`; };

  /* ============================================================== ROUTING */
  function go(v, opts) {
    if ((GATED.includes(v) || v === 'register') && !state.user) { v = 'landing'; opts = { scroll: 'register' }; }
    if (ROUTES[v]) { window.location.assign(ROUTES[v]); return; }
    state.view = v;
    if (location.hash !== '#' + v) history.pushState(null, '', '#' + v);
    render(); window.scrollTo({ top: 0, behavior: 'instant' });
    if (opts?.scroll) setTimeout(() => $(opts.scroll)?.scrollIntoView({ behavior: 'smooth', block: 'start' }), 60);
  }

  /* ============================================================== RENDER */
  function render() {
    const v = state.view;
    document.body.dataset.view = v;
    document.body.classList.toggle('registered', !!state.user);
    document.querySelectorAll('[data-view]').forEach((el) => { el.hidden = el.dataset.view !== v; });
    const titles = { landing: 'Recognising & Managing Depression in Primary Care', thanks: 'Registered', overview: 'Program overview', agenda: 'Agenda', cases: 'HCP case booklets', speakers: 'Speaker', live: 'Live session', hub: 'Post-event hub', takeaways: 'Key takeaways', certificate: 'Certificate', profile: 'Profile' };
    document.title = (titles[v] || 'DEPTH') + ' · DEPTH by Hetero';

    if ($('hdr-auth')) $('hdr-auth').innerHTML = state.user
      ? `<div class="hdr-nav"><span class="hdr-user">${esc(drName())}</span><button class="btn btn-soft sm" data-logout>Log out</button></div>`
      : `<nav class="hdr-nav"><a href="#overview" data-go="overview">Program</a><a href="#register" class="btn btn-grad sm" data-scroll="register">Register now</a></nav>`;
    if ($('tabs')) $('tabs').innerHTML = state.user && v !== 'landing' ? TABS.map(([k, l]) => `<button class="tab${(v === k || (['takeaways', 'certificate'].includes(v) && k === 'hub')) ? ' active' : ''}" data-go="${k}">${l}</button>`).join('') : '';
    stickyCta();

    if (v === 'landing') renderLanding();
    if (v === 'thanks') renderThanks();
    if (v === 'overview') renderOverview();
    if (v === 'agenda') renderAgenda();
    if (v === 'cases') renderCases();
    if (v === 'speakers') renderSpeakers();
    if (v === 'live') renderLive();
    if (v === 'hub') renderHub();
    if (v === 'takeaways') renderTakeaways();
    if (v === 'certificate') renderCertificate();
    if (v === 'profile') renderProfile();
    requestAnimationFrame(reveal);
  }

  /* sticky CTA: Register (not registered) / Join session (registered, live or upcoming) */
  function stickyCta() {
    const el = $('sticky-cta'); const s = status();
    if (!state.user) { el.innerHTML = `<span>Live session · ${EVENT.dateShort} · ${EVENT.time}</span><a class="btn btn-grad sm" href="#register" data-scroll="register">Register now →</a>`; el.hidden = true; el.dataset.watch = '1'; watchForm(); return; }
    delete el.dataset.watch;
    if (s === 'live') { el.innerHTML = `<span><span class="status s-live">Live now</span> ${esc(EVENT.full)}</span><button class="btn btn-grad sm" data-join="1">Join session →</button>`; el.hidden = false; return; }
    if (s === 'upcoming' && state.view !== 'live') { el.innerHTML = `<span>Live session · ${EVENT.dateShort} · ${EVENT.time}</span><button class="btn btn-line sm" data-go="live">Join link</button>`; el.hidden = false; return; }
    el.hidden = true;
  }

  /* show the register CTA only once the form has scrolled out of view */
  function watchForm() {
    const el = $('sticky-cta'), form = $('register');
    const check = () => { if (!el.dataset.watch || !form) return; const r = form.getBoundingClientRect(); el.hidden = r.bottom > 80 && r.top < innerHeight; };
    if (!watchForm.on) { addEventListener('scroll', check, { passive: true }); addEventListener('resize', check); watchForm.on = true; }
    check();
  }

  /* ---------- shared ---------- */
  const stripRow = () => `<tr><td><b>Live session</b></td><td>${EVENT.date} (${EVENT.day})</td><td>${EVENT.time}</td><td>${esc(EVENT.full)}</td></tr>`;
  function speakerMini(id) { const s = SPEAKERS[id]; return `<div class="spk-mini"><div class="spk-ph"><img src="${s.photo}" alt="${esc(s.name)}"></div><div><b>${esc(s.name)}</b><span>${esc(s.designation)}</span><span>${esc(s.designation2)}</span></div></div>`; }
  function webCard(cta) {
    return `<article class="web-card"><div class="wc-label"><span>Live session</span><i></i><em class="tagchip">Case-based webinar</em></div>
      <div class="wc-date">${ico('calendar')}<b>${EVENT.dateShort}</b><span>${EVENT.time}</span></div>
      <h3>${esc(EVENT.full)}</h3><p>${esc(EVENT.desc)}</p>${speakerMini(EVENT.speaker)}
      <div class="wc-foot">${pill()}${cta}</div></article>`;
  }
  function caseCard(c, compact) {
    return `<article class="case-card${compact ? ' compact' : ''}" style="--c:${c.color};--d:${(c.n - 1) * 80}ms">
      <div class="case-img"><img src="${c.img}" alt=""><span class="case-n">Case ${c.n}</span><span class="time-badge">${ico('clock')}${c.time}</span></div>
      <div class="case-body"><div class="case-tags"><span class="chip">${c.tag}</span><span class="chip">4-page PDF</span><span class="chip">Case-based learning</span></div>
      <h3>${esc(c.title)}</h3><p>${esc(c.sub)}</p>
      ${c.resourceIndex !== undefined
        ? `<button class="btn btn-line sm" data-resource="${c.resourceIndex}"${c.available ? '' : ' disabled'}>${c.available ? (c.time === 'Video' ? 'Watch video →' : 'View case →') : 'Coming soon'}</button>`
        : `<button class="btn btn-line sm" disabled>Coming soon</button>`}</div></article>`;
  }

  /* ---------- landing ---------- */
  function renderLanding() {
    const isd = $('reg-isd');
    if (isd && !isd.options.length) isd.innerHTML = ISD.map(([c, code, iso]) => `<option value="${code}" title="${c}"${code === '+63' ? ' selected' : ''}>${iso} ${code}</option>`).join('');
    countdown($('land-countdown'), EVENT.start, 'Live session opens in');
    const sel = $('reg-spec');
    if (!sel.options.length) sel.innerHTML = '<option value="">Select speciality</option>' + SPECIALITIES.map((s) => `<option>${s}</option>`).join('');
    const cn = $('reg-country');
    if (!cn.options.length) { cn.innerHTML = '<option value="">Select country</option>' + Object.keys(SEA).map((c) => `<option>${c}</option>`).join(''); fillCities(); cn.addEventListener('change', fillCities); }
  }
  function fillCities() {
    const c = $('reg-country').value, city = $('reg-city');
    const hit = ISD.find(([n]) => n === c); if (hit && $('reg-isd')) $('reg-isd').value = hit[1];
    city.innerHTML = '<option value="">' + (c ? 'Select city' : 'Select country first') + '</option>' + (SEA[c] || []).map((x) => `<option>${x}</option>`).join('');
    city.disabled = !c;
  }
  function setMode(login) { $('reg-card').hidden = login; $('login-card').hidden = !login; }

  /* ---------- thanks ---------- */
  function renderThanks() { $('thanks-rows').innerHTML = stripRow(); }

  /* ---------- overview ---------- */
  function renderOverview() {
    $('ov-welcome').textContent = `Welcome, ${drName()}`;
    $('ov-topics').innerHTML = TOPICS.map(([t, k, c], i) => `<div class="topic" style="--d:${i * 60}ms;--c:${c}"><span class="t-ico">${ico(k)}</span><p>${esc(t)}</p><span class="t-n">0${i + 1}</span></div>`).join('');
    const s = status(); const casesRead = (load('depth.cases', [])).length;
    const pct = Math.min(100, Math.round((casesRead / Math.max(1, CASES.length)) * 60 + (s === 'replay' ? 40 : 0)));
    $('ov-bar').style.width = pct + '%'; $('ov-pct').textContent = pct + '%';
    $('ov-progress-note').textContent = `${casesRead} of ${CASES.length} case booklets opened · live session ${s === 'replay' ? 'completed' : 'upcoming'}.`;
    $('ov-next').innerHTML = s === 'replay' ? '<b>The live session has concluded.</b><div class="small muted">Recording, post-read and your certificate are in the post-event hub.</div><div class="btn-row" style="margin-top:12px"><button class="btn btn-line sm" data-go="hub">Post-event hub</button></div>'
      : `<b>Live session — ${esc(EVENT.title)}</b><div class="small muted">${EVENT.date} · ${EVENT.time}</div><div class="countdown" id="ov-cd"></div><div class="btn-row" style="margin-top:14px"><button class="btn btn-line sm" data-ics="1">Add to calendar</button><button class="btn btn-soft sm" data-go="live">Join link</button></div>`;
    if (s !== 'replay') countdown($('ov-cd'), EVENT.start, 'Opens in');
    $('ov-strip').innerHTML = stripRow();
    $('ov-cards').innerHTML = `<article class="web-card ov"><img class="wc-img" src="${EVENT.img}" alt=""><div class="wc-body"><div class="wc-label"><span>Live session</span><i></i><em class="tagchip">Case-based webinar</em></div><h3>${esc(EVENT.full)}</h3><div class="wc-date">${ico('calendar')}<b>${EVENT.dateShort}</b><span>${EVENT.time}</span></div><p><b>Learning proposition —</b> ${esc(EVENT.proposition)}</p>${speakerMini(EVENT.speaker)}<div class="wc-foot">${pill()}<span class="btn-row"><button class="btn btn-grad sm" data-go="live">Join now</button><button class="btn btn-line sm" data-go="agenda">View details →</button></span></div></div></article>`;
    $('ov-cases').innerHTML = CASES.map((c) => caseCard(c, true)).join('');
  }

  /* ---------- agenda ---------- */
  function renderAgenda() {
    let t = Date.parse(EVENT.start);
    const rows = EVENT.agenda.map((row, i) => {
      const item = Array.isArray(row)
        ? { duration: row[0], title: row[1], names: row[2] }
        : row;
      const duration = Math.max(0, Number.parseInt(item.duration, 10) || 0);
      const at = item.time ? agendaTime(item.time) : fmtTime(new Date(t));
      if (!item.time && duration) t += duration * 60000;
      const title = item.title || item.role || 'Agenda item';
      const who = [item.role, item.names].filter(Boolean).join(' · ');
      return `<div class="ag-row" style="--d:${i * 50}ms"><div class="ag-time"><b>${esc(at)}</b><small>${duration ? String(duration).padStart(2, '0') + ' min' : 'Duration TBA'}</small></div><div class="ag-main"><h4>${esc(title)}</h4>${who ? `<span>${esc(who)}</span>` : ''}</div></div>`;
    }).join('');
    $('agenda-blocks').innerHTML = `<section class="ag-block"><div class="ag-head"><div><div class="wc-label"><span>Live session</span><i></i></div><h2 class="h2">${esc(EVENT.full)}</h2><div class="meta">${EVENT.date} · ${EVENT.time}</div></div><div class="btn-row">${pill()}<button class="btn btn-line sm" data-ics="1">Add to calendar</button></div></div><div class="ag-list">${rows}</div></section>`;
  }
  const agendaTime = (value) => {
    const [hours, minutes] = String(value).split(':').map(Number);
    if (!Number.isFinite(hours) || !Number.isFinite(minutes)) return String(value);
    const date = new Date(2000, 0, 1, hours, minutes);
    return date.toLocaleTimeString('en-IN', { hour: 'numeric', minute: '2-digit' }).toUpperCase();
  };
  const fmtTime = (d) => d.toLocaleTimeString('en-IN', { hour: 'numeric', minute: '2-digit', timeZone: 'Asia/Kolkata' }).toUpperCase();

  /* ---------- cases ---------- */
  function renderCases() { $('case-cards').innerHTML = CASES.map((c) => caseCard(c)).join(''); }

  /* ---------- speaker ---------- */
  function renderSpeakers() {
    $('speaker-cards').innerHTML = Object.values(SPEAKERS).map((s) => `<article class="spk-full">
      <div class="spk-left"><div class="spk-photo"><img src="${s.photo}" alt="${esc(s.name)}"></div><div class="kicker">${s.role} · Live session</div></div>
      <div class="spk-right"><h2 class="h2">${esc(s.name)}</h2><p class="spk-des">${esc(s.designation)}<br>${esc(s.designation2)}</p><p class="body">${s.bio}</p>
        <div class="kicker" style="margin-top:18px">Areas of expertise</div><div class="chips">${s.expertise.map((e) => `<span class="chip">${esc(e)}</span>`).join('')}</div>
        <details class="profile-t"><summary>Speaker profile</summary><table class="kv">${s.profile.map(([k, vv]) => `<tr><th>${k}</th><td>${esc(vv)}</td></tr>`).join('')}</table></details>${s.resourceUrl ? `<a class="btn btn-line sm" href="${esc(s.resourceUrl)}" target="_blank" rel="noopener">Open speaker resource →</a>` : ''}</div></article>`).join('')
;
  }

  /* ---------- live ---------- */
  function renderLive() {
    const s = status();
    const cta = s === 'live'
      ? `<button class="btn btn-grad lg" data-join="1">Join webinar →</button>`
      : s === 'replay'
        ? (EVENT.recordingAvailable
          ? `<a class="btn btn-blue lg" href="${esc(EVENT.videoPageUrl)}">Watch recording</a>`
          : '<button class="btn btn-soft lg" disabled>Coming soon</button>')
        : `<button class="btn btn-soft lg" disabled>Join webinar</button><div class="countdown dark" id="live-cd"></div>`;
    const speakerNames = Object.values(SPEAKERS).map((speaker) => speaker.name).join(', ');
    $('live-cards').innerHTML = `<article class="live-card${s === 'live' ? ' is-live' : ''}"><img class="lc-img" src="${EVENT.img}" alt=""><div class="lc-body"><div class="wc-label light"><span>Live session</span><i></i></div><h2>${esc(EVENT.full)}</h2><table class="table mini"><tr><th>Date</th><th>Time</th><th>Speaker</th></tr><tr><td>${EVENT.date} (${EVENT.day})</td><td>${EVENT.time}</td><td>${esc(speakerNames)}</td></tr></table><div class="lc-foot">${pill()}${cta}</div></div></article>`;
    if (s === 'upcoming') countdown($('live-cd'), EVENT.start, 'Opens in', true);
    liveClock();
  }
  function liveClock() { const el = $('live-clock'); if (!el) return; const t = () => { el.textContent = 'Now: ' + new Date().toLocaleString('en-IN', { timeZone: 'Asia/Kolkata', hour: 'numeric', minute: '2-digit', day: 'numeric', month: 'short' }) + ' IST'; }; t(); clearInterval(liveClock.i); liveClock.i = setInterval(t, 30000); }

  /* ---------- hub ---------- */
  function renderHub() {
    const ready = status() === 'replay';
    const fullRecording = RESOURCES.find((resource) => resource.category === 'full_recording');
    const postRead = RESOURCES.find((resource) => resource.category === 'post_read');
    const snippet = RESOURCES.find((resource) => resource.category === 'snippet_video');
    const keyPearls = RESOURCES.find((resource) => resource.category === 'key_pearls');
    const references = RESOURCES.find((resource) => resource.category === 'references');
    const practical = RESOURCES.find((resource) => resource.category === 'practical_takeaways');
    const availableCases = resourceCases.filter(({ resource }) => resource.available);
    const resourceButton = (resource, label, css = 'btn btn-line sm') => {
      const index = resource ? RESOURCES.indexOf(resource) : -1;
      return resource?.available
        ? `<button class="${css}" data-resource="${index}">${label}</button>`
        : `<button class="${css}" disabled>Coming soon</button>`;
    };
    const recordingAction = EVENT.recordingAvailable
      ? `<a class="btn btn-grad sm" href="${esc(EVENT.videoPageUrl || '#live')}">Watch full session →</a>`
      : '<button class="btn btn-line sm" disabled>Coming soon</button>';
    const postReadAction = postRead?.available
      ? `<button class="btn btn-line sm" data-resource="${RESOURCES.indexOf(postRead)}">View post-read</button>`
      : '<button class="btn btn-line sm" disabled>Coming soon</button>';
    $('hub-recordings').innerHTML = `
      <article class="rec-card${ready ? '' : ' locked'}"><div class="rec-img"><img src="${depthAsset('assets/visual/brain-red-2.jpg')}" alt=""><div class="play"><span></span></div><span class="time-badge">${ico('clock')}${esc(EVENT.duration || '~60 min')}</span></div><div class="rec-body"><div class="wc-label"><span>Full webinar recording</span><i></i></div><h3>Watch the full session</h3><p>${esc(fullRecording?.label || EVENT.full)}</p><div class="small muted">Webinar date ${EVENT.date}</div><div class="wc-foot">${recordingAction}</div></div></article>
      <div class="syn-card"><div class="wc-label"><span>1-page post-read / synopsis</span><i></i></div><h3>${esc(postRead?.label || EVENT.title)}</h3><p>${esc(EVENT.desc)}</p><div class="kicker">Key discussion areas</div><ul>${EVENT.synopsis.map((x) => `<li>${esc(x)}</li>`).join('')}</ul><div class="btn-row">${postReadAction}<button class="btn btn-soft sm" data-go="takeaways">Key takeaways →</button></div></div>`;
    const learningCards = [
      ['pearl', 'Key clinical pearls', keyPearls?.description || 'Short, memorable messages from the session — recognition, assessment and next steps.', resourceButton(keyPearls, 'Explore')],
      ['refs', 'Selected evidence / references', references?.description || 'The studies and guidelines cited by the speaker, with links.', resourceButton(references, 'Explore')],
      ['practice', 'Practical takeaways', practical?.description || 'Five key takeaways from the session, designed for easy consumption and sharing.', '<button class="btn btn-line sm" data-go="takeaways">View takeaways</button>'],
      ['case', `HCP case booklets 1–${CASES.length}`, 'Revisit the four case booklets that anchor the discussion.', availableCases.length ? '<button class="btn btn-line sm" data-go="cases">Open the cases</button>' : '<button class="btn btn-line sm" disabled>Coming soon</button>'],
    ];
    const certificateText = ready ? (EVENT.certificateUnlocked ? 'Your personalised certificate is ready to download.' : 'Complete the full session to unlock your certificate.') : 'Available after event.';
    const certificateButton = ready && EVENT.certificateUnlocked
      ? `<a class="btn btn-grad sm" href="${esc(EVENT.certificateUrl)}">Download</a>`
      : `<button class="btn btn-grad sm" disabled>${ready ? 'Locked' : 'Available after event'}</button>`;
    learningCards.push(['cert', 'Certificate of participation', certificateText, certificateButton]);
    $('hub-postread').innerHTML = learningCards.map(([k, t, d, b], i) => `<div class="pr-card" style="--d:${i * 70}ms"><span class="t-ico">${ico(k)}</span><div><b>${esc(t)}</b><p>${esc(d)}</p>${b}</div></div>`).join('');
    const recordingReady = EVENT.recordingAvailable;
    const res = [[fullRecording?.label || 'Full Webinar Recording', 'Video', recordingReady ? `<a class="link" href="${esc(EVENT.videoPageUrl || '#live')}">Watch</a>` : '<button class="link" disabled>Coming soon</button>']];
    res.push([postRead?.label || '1-Page Post-read / Synopsis', 'PDF', resourceButton(postRead, 'View', 'link')]);
    res.push([snippet?.label || 'Snippet videos', 'Video', resourceButton(snippet, 'Watch', 'link')]);
    res.push([`HCP Case Booklets 1–${CASES.length}`, `${CASES.length} × 4-page PDFs`, availableCases.length ? '<button class="link" data-go="cases">Open</button>' : '<button class="link" disabled>Coming soon</button>']);
    res.push(['Certificate of participation', 'PDF', ready && EVENT.certificateUnlocked ? `<a class="link" href="${esc(EVENT.certificateUrl)}">Download</a>` : `<button class="link" disabled>${ready ? 'Locked' : 'Available after event'}</button>`]);
    $('hub-resources').innerHTML = res.map((r) => `<tr><td><b>${r[0]}</b></td><td>${r[1]}</td><td class="r">${r[2]}</td></tr>`).join('');
  }

  /* ---------- takeaways ---------- */
  function renderTakeaways() {
    $('tk-title').textContent = `Key takeaways — ${EVENT.title}`;
    $('tk-note').textContent = status() === 'replay' ? EVENT.date : 'Draft — final takeaways are published after the session.';
    const labels = { evidence: 'Evidence-based clinical insight', assessment: 'Patient assessment / diagnostic insight', treatment: 'Treatment / management consideration', case: 'Case-based learning point', practice: 'Practical / forward-looking insight' };
    $('tk-list').innerHTML = EVENT.takeaways.map(([k, t], i) => `<li style="--d:${i * 80}ms"><span class="tk-ico">${ico(k)}</span><div><small>0${i + 1} · ${labels[k]}</small><p>${esc(t)}</p></div></li>`).join('');
    const recordingButton = EVENT.recordingAvailable
      ? `<a class="btn btn-grad" href="${esc(EVENT.videoPageUrl)}">Watch recording →</a>`
      : '<button class="btn btn-grad" disabled>Coming soon</button>';
    document.querySelector('.std-band').innerHTML = `<div><div class="kicker">Revisit the full session</div><h3>Watch the recording</h3></div>${recordingButton}`;
  }

  /* ---------- certificate ---------- */
  function renderCertificate() {
    const ready = status() === 'replay' && EVENT.certificateUnlocked;
    $('cert-name').textContent = drName();
    $('cert-actions').innerHTML = ready
      ? `<a class="btn btn-grad" href="${esc(EVENT.certificateUrl)}">Download PDF</a>`
      : `<span class="status s-upcoming">${status() === 'replay' ? 'Complete the full session to unlock' : 'Available after event'}</span>`;
    $('cert-card').classList.toggle('locked', !ready);
  }

  /* ---------- profile ---------- */
  function renderProfile() {
    $('pf-name').textContent = drName();
  }

  /* ============================================================== WIDGETS */
  function countdown(el, iso, label, dark) {
    if (!el) return; clearInterval(el._t);
    const tick = () => {
      const d = Date.parse(iso) - Date.now();
      if (d <= 0) { el.innerHTML = `<span class="label">${label.replace(/ opens in| in/, '')}</span><span class="status s-live">Live now</span>`; clearInterval(el._t); return; }
      const dd = Math.floor(d / 864e5), hh = Math.floor(d / 36e5) % 24, mm = Math.floor(d / 6e4) % 60, ss = Math.floor(d / 1e3) % 60;
      el.innerHTML = `<span class="label">${label}</span><div class="cd">${[[dd, 'days'], [hh, 'hrs'], [mm, 'min'], [ss, 'sec']].map(([v, l]) => `<div><b>${String(v).padStart(2, '0')}</b><span>${l}</span></div>`).join('')}</div>`;
    };
    el.className = 'countdown' + (dark ? ' dark' : ''); tick(); el._t = setInterval(tick, 1000);
  }

  let io;
  try { io = new IntersectionObserver((es) => es.forEach((e) => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } }), { threshold: 0.12 }); } catch (e) { io = null; }
  function reveal() { document.querySelectorAll('[data-view]:not([hidden]) .web-card, [data-view]:not([hidden]) .topic, [data-view]:not([hidden]) .case-card, [data-view]:not([hidden]) .ag-row, [data-view]:not([hidden]) .rec-card, [data-view]:not([hidden]) .syn-card, [data-view]:not([hidden]) .pr-card, [data-view]:not([hidden]) .tk-list li, [data-view]:not([hidden]) .live-card, [data-view]:not([hidden]) .spk-full, [data-view]:not([hidden]) .panel-card').forEach((el) => { if (!el.classList.contains('reveal')) { el.classList.add('reveal'); io ? io.observe(el) : el.classList.add('in'); } }); }

  /* ============================================================== ACTIONS */
  function toast(msg) { const t = $('toast'); t.textContent = msg; t.hidden = false; clearTimeout(toast.i); toast.i = setTimeout(() => { t.hidden = true; }, 2600); }
  function modal(kind, title) {
    $('modal-title').textContent = title;
    $('modal-body').textContent = kind === 'video' ? 'The video will be published here. Registered participants receive a notification on their registered email when it goes live.' : 'The PDF will be available for download here once published.';
    $('modal-art').className = 'modal-video' + (kind === 'pdf' ? ' is-pdf' : '');
    $('modal-art').innerHTML = '<div class="play"><span></span></div>';
    $('modal').hidden = false;
    if (/^Case Booklet (\d)/.test(title)) { const n = +RegExp.$1; const seen = load('depth.cases', []); if (!seen.includes(n)) { seen.push(n); save('depth.cases', seen); } }
  }
  function openResource(index) {
    const resource = RESOURCES[Number(index)];
    if (!resource?.url) return;
    if (!resource.available) { toast('This resource is not available yet'); return; }
    if (resource.category === 'case_book') {
      const caseNumber = resourceCases.findIndex((item) => item.resourceIndex === Number(index)) + 1;
      const seen = load('depth.cases', []);
      if (caseNumber > 0 && !seen.includes(caseNumber)) { seen.push(caseNumber); save('depth.cases', seen); }
    }
    if (resource.embedUrl || resource.isVideo) {
      $('modal-title').textContent = resource.label;
      $('modal-body').textContent = resource.type;
      $('modal-art').className = 'modal-video';
      $('modal-art').innerHTML = resource.embedUrl
        ? `<iframe src="${esc(resource.embedUrl)}" title="${esc(resource.label)}" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe>`
        : `<video src="${esc(resource.url)}" controls playsinline></video>`;
      $('modal').hidden = false;
      return;
    }
    window.open(resource.url, '_blank', 'noopener,noreferrer');
  }
  function openGoogleCalendar() {
    const dt = (iso) => new Date(iso).toISOString().replace(/[-:]/g, '').replace(/\.\d{3}/, '');
    const details = EVENT.desc + (EVENT.joinUrl ? `\n\nJoin webinar: ${EVENT.joinUrl}` : '');
    const params = new URLSearchParams({
      action: 'TEMPLATE',
      text: `DEPTH live session — ${EVENT.full}`,
      dates: `${dt(EVENT.start)}/${dt(EVENT.end)}`,
      details,
      location: 'Online (live webinar)',
    });
    window.open(`https://calendar.google.com/calendar/u/0/r/eventedit?${params.toString()}`, '_blank', 'noopener,noreferrer');
  }

  document.addEventListener('click', (e) => {
    const t = e.target.closest('[data-go],[data-scroll],[data-ics],[data-modal],[data-resource],[data-logout],[data-show-login],[data-show-register],[data-print],[data-join],[data-close]');
    if (!t) { if (e.target === $('modal')) $('modal').hidden = true; return; }
    if (t.dataset.go !== undefined) { e.preventDefault(); go(t.dataset.go); return; }
    if (t.dataset.scroll) { e.preventDefault(); if (state.view !== 'landing') { state.view = 'landing'; history.pushState(null, '', '#' + t.dataset.scroll); render(); } setTimeout(() => document.getElementById(t.dataset.scroll)?.scrollIntoView({ behavior: 'smooth', block: 'start' }), 30); return; }
    if (t.dataset.ics) { openGoogleCalendar(); return; }
    if (t.dataset.modal) { modal(t.dataset.modal, t.dataset.title || ''); return; }
    if (t.dataset.resource !== undefined) { openResource(t.dataset.resource); return; }
    if (t.dataset.close !== undefined) { $('modal').hidden = true; $('modal-art').innerHTML = '<div class="play"><span></span></div>'; return; }
    if (t.dataset.join) {
      if (!EVENT.joinUrl) { toast('Join link has not been published yet'); return; }
      const joinUrl = new URL(EVENT.joinUrl, window.location.href);
      if (joinUrl.origin === window.location.origin) window.location.assign(joinUrl.href);
      else window.open(joinUrl.href, '_blank', 'noopener,noreferrer');
      return;
    }
    if (t.dataset.print) { document.body.dataset.print = t.dataset.print; window.print(); return; }
    if (t.dataset.logout !== undefined) { state.user = null; save('depth.user', null); window.location.assign(window.DEPTH_LOGOUT_URL || '/'); return; }
    if (t.dataset.showLogin !== undefined) { setMode(true); return; }
    if (t.dataset.showRegister !== undefined) { setMode(false); return; }
  });
  window.addEventListener('afterprint', () => { delete document.body.dataset.print; });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') $('modal').hidden = true; });

  $('reg-form')?.addEventListener('submit', (e) => {
    e.preventDefault(); const f = e.target;
    const need = ['reg-name', 'reg-spec', 'reg-inst', 'reg-country', 'reg-city', 'reg-email'];
    let bad = need.find((id) => !$(id).value.trim()) || (!$('reg-email').validity.valid ? 'reg-email' : null);
    if (!$('reg-consent').checked) bad = bad || 'reg-consent';
    f.querySelectorAll('.input').forEach((i) => i.classList.remove('bad'));
    if (bad) { const el = $(bad); el.classList.add('bad'); el.focus(); toast('Please complete all required fields'); return; }
    state.user = { name: $('reg-name').value.trim(), spec: $('reg-spec').value, inst: $('reg-inst').value.trim(), country: $('reg-country').value, city: $('reg-city').value, email: $('reg-email').value.trim(), mobile: ($('reg-mobile').value.trim() ? $('reg-isd').value + ' ' + $('reg-mobile').value.trim() : ''), event: '1', registered: new Date().toISOString() };
    save('depth.user', state.user); go('thanks');
  });
  $('login-form')?.addEventListener('submit', (e) => {
    e.preventDefault(); const id = $('login-id').value.trim().toLowerCase();
    if (!id) { $('login-id').classList.add('bad'); return; }
    const u = load('depth.user', null);
    if (u && (u.email.toLowerCase() === id || u.mobile.replace(/\s/g, '') === id.replace(/\s/g, ''))) state.user = u;
    else { const isEmail = id.includes('@'); state.user = { name: isEmail ? id.split('@')[0].replace(/[._-]+/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase()) : 'Participant', spec: '', inst: '', city: '', email: isEmail ? id : '', mobile: isEmail ? '' : id, event: '1', registered: new Date().toISOString(), openLogin: true }; }
    save('depth.user', state.user); go('overview'); toast(`Welcome back, ${drName()}`);
  });

  /* ============================================================== BOOT */
  try { history.scrollRestoration = 'manual'; } catch (e) {}
  const legacyView = location.hash.replace('#', '');
  if (legacyView && ROUTES[legacyView] && legacyView !== window.DEPTH_INITIAL_VIEW) {
    window.location.replace(ROUTES[legacyView]);
    return;
  }
  const first = window.DEPTH_INITIAL_VIEW || location.hash.replace('#', '') || 'overview';
  state.view = first;
  render();
  if (window.DEPTH_FLASH_SUCCESS) setTimeout(() => toast(window.DEPTH_FLASH_SUCCESS), 120);
  if (first === 'register') setTimeout(() => $('register')?.scrollIntoView(), 80);
})();
