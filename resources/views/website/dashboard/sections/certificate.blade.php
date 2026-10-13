<section class="view wrap" data-view="certificate">
  <div class="member-head no-print"><div><div class="kicker">Certificate of participation</div><h1 class="h1">Your certificate</h1></div><div class="btn-row" id="cert-actions"></div></div>
  <div class="cert-card" id="cert-card">
    <div class="cert-in">
      <div class="cert-top"><img src="{{ asset('web/depth/assets/hetero.png') }}" alt="Hetero"><span class="cert-wm"><b>DEPTH</b><span>Depression Education Program for Therapeutic Excellence by Hetero</span></span></div>
      <div class="cert-title">Certificate</div><div class="cert-sub">of participation</div>
      <p class="cert-txt">This certificate is awarded to</p><div class="cert-name" id="cert-name">Dr. Participant</div>
      <p class="cert-txt">in recognition of active participation in the international webinar</p><div class="cert-prog">"Recognising &amp; Managing Depression in Primary Care"</div><p class="cert-txt">held on {{ $depthEvent['date'] ?? 'the scheduled date' }}</p>
      <div class="cert-sig"><div><span class="cert-line"></span><b>{{ $depthSpeakers->first()['name'] ?? 'Faculty' }}</b><small>{{ $depthSpeakers->first()['designation'] ?? 'Speaker' }}</small></div><div><span class="cert-line"></span><b>Medical Team</b><small>Hetero Healthcare</small></div></div>
    </div>
  </div>
</section>
