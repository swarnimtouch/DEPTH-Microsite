@extends('layouts.website')
@section('standalone_page', 'true')

@push('styles')
<link rel="stylesheet" href="{{ asset('web/depth/style.css') }}?v={{ filemtime(public_path('web/depth/style.css')) }}">
<style>
    .depth-room-header{position:sticky;top:0;z-index:1030;background:rgba(255,255,255,.97);border-bottom:1px solid #e1e5ec;backdrop-filter:blur(10px)}
    .depth-room-header__top{max-width:1260px;margin:0 auto;padding:12px 28px;display:flex;align-items:center;justify-content:space-between;gap:24px}
    .depth-room-brand{display:flex;align-items:center;gap:16px;text-decoration:none;min-width:0}.depth-room-logo{display:block;width:auto;height:48px}.depth-room-brand__divider{width:1px;height:46px;background:#d9dee7;flex:0 0 auto}.depth-room-depth-logo{display:block;width:auto;height:48px;max-width:250px}
    .depth-room-user{display:flex;align-items:center;gap:20px;color:#1f2a44;font-size:12px;font-weight:700;letter-spacing:.04em}.depth-room-logout{display:inline-flex;align-items:center;justify-content:center;padding:10px 16px;border:1px solid #d2d8e2;border-radius:10px;color:#1f2a44;text-decoration:none;text-transform:uppercase;letter-spacing:.08em;font-size:11px;font-weight:800;background:#fff}
    .depth-room-tabs{border-top:1px solid #e1e5ec;overflow-x:auto;scrollbar-width:none}.depth-room-tabs::-webkit-scrollbar{display:none}.depth-room-tabs__inner{max-width:1260px;margin:0 auto;padding:0 28px;display:flex;align-items:center}.depth-room-tab{position:relative;display:block;padding:14px 16px;color:#6e7587;text-decoration:none;text-transform:uppercase;letter-spacing:.08em;font-size:12px;font-weight:700;white-space:nowrap}.depth-room-tab:hover,.depth-room-tab.is-active{color:#1f2a44}.depth-room-tab::after{content:"";position:absolute;left:16px;right:16px;bottom:0;height:2px;background:#d71920;transform:scaleX(0);transform-origin:left}.depth-room-tab:hover::after,.depth-room-tab.is-active::after{transform:scaleX(1)}
    .survey-response-page{min-height:100vh;background:var(--bg,#fff);font-family:Inter,Poppins,sans-serif}
    .depth-room-tab,.depth-room-tab:hover,.depth-room-tab:focus,.depth-room-tab:active{border-bottom:0!important;text-decoration:none!important}
    .survey-review-page{flex:1;background:var(--bg,#fff);padding:48px 18px 72px}
    .survey-review-shell{max-width:780px;margin:0 auto}
    .survey-review-card{background:#fff;border:1px solid #ded8d1;border-radius:14px;padding:32px;box-shadow:0 8px 28px rgba(31,42,68,.05)}
    .survey-review-kicker{color:#a8283f;text-transform:uppercase;font-size:11px;font-weight:800;letter-spacing:.12em}
    .survey-review-saved{background:#edf9f2;color:#21824f;border-radius:8px;padding:12px;margin:24px 0}
    .survey-review-progress{height:5px;background:#e9e5e1;border-radius:10px;overflow:hidden}.survey-review-progress span{display:block;height:100%;background:#a8283f;transition:width .2s ease}
    .survey-review-option{display:flex;align-items:center;gap:12px;padding:15px;margin-top:10px;border:1px solid #dedbd7;border-radius:10px;color:#1f2a44}
    .survey-review-option.is-selected{border-color:#a8283f;background:#fff7f8}.survey-review-check{margin-left:auto;color:#a8283f}
    .survey-matrix-scroll{overflow-x:auto;border:1px solid #dedbd7;border-radius:10px}.survey-matrix{width:100%;min-width:720px;border-collapse:collapse}.survey-matrix th,.survey-matrix td{padding:12px;border-bottom:1px solid #e8e4e0;border-right:1px solid #e8e4e0;text-align:center;vertical-align:middle}.survey-matrix tr:last-child th,.survey-matrix tr:last-child td{border-bottom:0}.survey-matrix th:last-child,.survey-matrix td:last-child{border-right:0}.survey-matrix thead th{background:#f6f3f1;font-size:12px}.survey-matrix .survey-matrix-row{text-align:left;min-width:260px}.survey-matrix-choice{display:inline-grid;place-items:center;width:24px;height:24px;border:1px solid #cbc5bf;border-radius:50%;color:#a8283f}.survey-matrix-choice.is-selected{border-color:#a8283f;background:#fff3f5}
    @media(max-width:760px){.depth-room-header__top,.depth-room-tabs__inner{padding-left:18px;padding-right:18px}.depth-room-brand{gap:9px;flex:1 1 auto}.depth-room-logo{height:36px}.depth-room-brand__divider{height:34px}.depth-room-depth-logo{height:34px;max-width:min(36vw,150px)}.depth-room-user__name{display:none}.depth-room-tab{padding:12px 13px;font-size:10px}.survey-review-card{padding:22px}}
</style>
@endpush

@section('body')
<div class="page survey-response-page">
@include('partials.website.depth_member_header')
<main class="survey-review-page"><div class="survey-review-shell">
    <a href="{{ route('modules.show', $module->slug) }}" class="d-inline-block mb-3 text-decoration-none">&larr; Back to live session</a>
    <section class="survey-review-card">
        <div class="survey-review-kicker">{{ $surveyType === 'pre' ? 'Before the session' : 'After the session' }}</div>
        <h2 class="fw-bold text-dark-green mt-2 mb-1">{{ ucfirst($surveyType) }}-event survey</h2>
        <p class="text-muted mb-0">{{ $module->title }}</p>
        <div class="d-flex justify-content-between small mt-4 mb-2"><strong class="text-danger" id="reviewCount"></strong><span class="text-muted">Your response</span></div>
        <div class="survey-review-progress mb-4"><span id="reviewProgress"></span></div>
        <h4 class="fw-bold text-dark-green" id="reviewQuestion"></h4>
        <div id="reviewOptions"></div>
        <div class="d-flex justify-content-between mt-4 pt-3 border-top">
            <button type="button" class="btn btn-light" id="reviewPrevious">Previous</button>
            <button type="button" class="btn btn-primary-custom px-4" id="reviewNext">Next question &rarr;</button>
        </div>
    </section>
</div></main>
@include('partials.website.depth_dashboard_footer')
</div>
@endsection

@push('scripts')
<script>
const reviewAnswers = @json($responses);
let reviewIndex = 0;
function escapeReview(value){return String(value ?? '').replace(/[&<>"']/g,char=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));}
function renderMatrixReview(item){
    const headings=item.options.map(option=>`<th scope="col">${escapeReview(option)}</th>`).join('');
    const rows=item.rows.map(row=>`<tr><th scope="row" class="survey-matrix-row">${escapeReview(row.row)}</th>${item.options.map(option=>`<td><span class="survey-matrix-choice ${option===row.answer?'is-selected':''}">${option===row.answer?'<i class="bi bi-check"></i>':''}</span></td>`).join('')}</tr>`).join('');
    return `<div class="survey-matrix-scroll"><table class="survey-matrix"><thead><tr><th scope="col" class="survey-matrix-row">Clinical condition</th>${headings}</tr></thead><tbody>${rows}</tbody></table></div>`;
}
function renderReview(){
    const item=reviewAnswers[reviewIndex];
    document.getElementById('reviewCount').textContent=`Question ${reviewIndex+1} of ${reviewAnswers.length}`;
    document.getElementById('reviewProgress').style.width=`${((reviewIndex+1)/reviewAnswers.length)*100}%`;
    document.getElementById('reviewQuestion').textContent=item.question;
    document.getElementById('reviewOptions').innerHTML=item.type==='matrix_single_choice'
        ? renderMatrixReview(item)
        : item.options.map(option=>`<div class="survey-review-option ${option===item.answer?'is-selected':''}"><span class="border rounded-circle" style="width:18px;height:18px"></span><span>${escapeReview(option)}</span>${option===item.answer?'<i class="bi bi-check-circle-fill survey-review-check"></i>':''}</div>`).join('');
    document.getElementById('reviewPrevious').classList.toggle('invisible',reviewIndex===0);
    document.getElementById('reviewNext').textContent=reviewIndex===reviewAnswers.length-1?'Back to live session':'Next question →';
}
document.getElementById('reviewPrevious').addEventListener('click',()=>{if(reviewIndex>0){reviewIndex--;renderReview();}});
document.getElementById('reviewNext').addEventListener('click',()=>{if(reviewIndex<reviewAnswers.length-1){reviewIndex++;renderReview();}else{window.location.href=@json(route('modules.show',$module->slug));}});
renderReview();
</script>
@endpush
