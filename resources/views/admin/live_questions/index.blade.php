@extends('layouts.admin')

@push('styles')
<style>
  .live-question-actions{display:flex;flex-wrap:wrap;gap:6px;align-items:center}
  #questionAnalyticsModal .modal-dialog{max-width:1140px}
  #questionAnalyticsModal .modal-header{padding:22px 24px 18px}
  #questionAnalyticsModal .modal-body{padding:20px 24px 26px}
  #questionAnalyticsModal .modal-footer{padding:18px 24px}
  .question-analytics-chart{min-height:380px;padding:12px;background:#fff;border:1px solid #e7ebf2;border-radius:8px}
  .question-analytics-empty{padding:48px 20px;text-align:center;background:#f8fafc;border:1px dashed #d8deea;border-radius:10px;color:#7e8299}
  .question-summary-card{min-height:80px;padding:16px 18px;border-radius:8px;background:#eef8ff}
  .question-summary-card span{display:block;color:#7e8299;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em}
  .question-summary-card strong{display:block;margin-top:5px;color:#181c32;font-size:24px}
  .analytics-question-heading{font-size:17px;line-height:1.5}
  .analytics-meta-badge{font-size:10px;letter-spacing:.04em;text-transform:uppercase}
  .analytics-question-text{white-space:normal;overflow-wrap:anywhere;line-height:1.55}
  .analytics-response-table{min-width:1120px;table-layout:fixed}
  .analytics-response-table th,.analytics-response-table td{vertical-align:top!important}
  .analytics-response-table td{white-space:normal;overflow-wrap:anywhere;line-height:1.5}
  .analytics-response-table .analytics-date{white-space:nowrap}
</style>
@endpush

@section('content')
@php($canManageLiveQuestions = auth('admin')->user()?->type === 'admin')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
  <div class="post d-flex flex-column-fluid"><div class="container-xxl">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="card">
      <div class="card-header border-0 pt-6">
        <div class="card-title"><h2>Live Polls &amp; Quizzes</h2></div>
        <div class="card-toolbar">
          <a href="{{ route('admin.live_questions.logs') }}" class="btn btn-light-primary me-2">View Logs</a>
          @if($canManageLiveQuestions)<a href="{{ route('admin.live_questions.form') }}" class="btn btn-primary">Add Question</a>@endif
        </div>
      </div>
      <div class="card-body pt-0">
        <div class="table-responsive"><table class="table align-middle table-row-dashed fs-6 gy-5">
          <thead><tr class="text-muted fw-bolder text-uppercase fs-7"><th>Session</th><th>Question</th><th>Type</th><th>Responses</th><th>Start / Stop</th><th>Actions</th></tr></thead>
          <tbody>
          @forelse($questions as $question)
            <tr>
              <td>{{ $question->module?->title ?? '-' }}</td>
              <td><div class="fw-bold" style="max-width:420px">{{ $question->question }}</div><span class="badge badge-light-{{ $question->status === 'live' ? 'success' : ($question->status === 'stopped' ? 'danger' : 'secondary') }}">{{ ucfirst($question->status) }}</span></td>
              <td><span class="badge badge-light-primary">{{ ucfirst($question->type) }}</span></td>
              <td>{{ $question->responses_count }}</td>
              <td>
                @if($canManageLiveQuestions)
                  <label class="form-check form-switch form-check-custom form-check-solid"><input class="form-check-input live-toggle" type="checkbox" data-url="{{ route('admin.live_questions.toggle', $question) }}" {{ $question->status === 'live' ? 'checked' : '' }}><span class="form-check-label">{{ $question->status === 'live' ? 'Live' : 'Stopped' }}</span></label>
                @else
                  <span class="badge badge-light-{{ $question->status === 'live' ? 'success' : 'secondary' }}">{{ $question->status === 'live' ? 'Live' : 'Stopped' }}</span>
                @endif
              </td>
              <td><div class="live-question-actions">
                <button
                  type="button"
                  class="btn btn-sm btn-light-primary view-question-chart"
                  data-analytics-url="{{ route('admin.live_questions.analytics', $question) }}"
                  data-export-url="{{ route('admin.live_questions.responses.export', $question) }}"
                ><i class="bi bi-bar-chart-fill me-1"></i>View Chart</button>
                @if($canManageLiveQuestions)
                  <a href="{{ route('admin.live_questions.form', $question) }}" class="btn btn-sm btn-light-primary">Edit</a>
                  <button class="btn btn-sm btn-light-danger delete-question" data-url="{{ route('admin.live_questions.destroy', $question) }}">Delete</button>
                @else
                  <span class="text-muted fs-8">View only</span>
                @endif
              </div></td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-muted py-10">No live questions added yet.</td></tr>
          @endforelse
          </tbody>
        </table></div>
        {{ $questions->links() }}
      </div>
    </div>
  </div></div>
</div>

<div class="modal fade" id="questionAnalyticsModal" tabindex="-1" aria-labelledby="questionAnalyticsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <div class="pe-4">
          <h2 class="modal-title fs-3 fw-bolder" id="questionAnalyticsModalLabel">Poll / Quiz Results</h2>
          <div class="text-muted fs-7 mt-1 analytics-question-text" data-analytics-module></div>
        </div>
        <button type="button" class="btn btn-sm btn-icon btn-active-light-primary" data-bs-dismiss="modal" aria-label="Close"><i class="bi bi-x-lg"></i></button>
      </div>
      <div class="modal-body">
        <div class="d-flex align-items-center justify-content-center py-15" data-analytics-loading><span class="spinner-border text-primary me-3"></span><span class="text-muted">Loading analytics...</span></div>
        <div class="alert alert-danger d-none" data-analytics-error></div>
        <div class="d-none" data-analytics-content>
          <div class="d-flex align-items-center gap-2 mb-3">
            <span class="badge badge-light-primary analytics-meta-badge" data-analytics-type></span>
            <span class="badge badge-light-secondary analytics-meta-badge" data-analytics-status></span>
          </div>
          <h3 class="fw-bolder text-gray-800 analytics-question-heading analytics-question-text mb-5" data-analytics-question></h3>

          <div data-analytics-results>
            <div class="row g-4 align-items-stretch mb-8">
              <div class="col-lg-8"><div class="question-analytics-chart h-100" data-analytics-chart></div></div>
              <div class="col-lg-4">
                <div class="d-grid gap-3">
                  <div class="question-summary-card"><span>Total Responses</span><strong data-analytics-total>0</strong></div>
                  <div data-quiz-summary><div class="question-summary-card"><span>Correct Responses</span><strong class="text-success" data-analytics-correct>0</strong></div></div>
                  <div data-quiz-summary><div class="question-summary-card"><span>Incorrect Responses</span><strong class="text-danger" data-analytics-incorrect>0</strong></div></div>
                </div>
              </div>
            </div>
            <h3 class="fs-4 fw-bolder mb-4">Who answered what</h3>
            <div class="table-responsive">
              <table class="table table-row-dashed align-middle gy-4 analytics-response-table">
                <colgroup><col style="width:220px"><col style="width:260px"><col style="width:150px"><col style="width:270px"><col style="width:105px"><col style="width:150px"></colgroup>
                <thead><tr class="text-muted fw-bolder fs-7 text-uppercase"><th>Participant</th><th>Email</th><th>Mobile</th><th>Selected Answer</th><th>Result</th><th>Answered At</th></tr></thead>
                <tbody data-analytics-responses></tbody>
              </table>
            </div>
          </div>
          <div class="question-analytics-empty d-none" data-analytics-empty><i class="bi bi-bar-chart fs-2x d-block mb-3"></i><strong class="d-block text-gray-700 mb-1">No responses received yet</strong><span>The chart and participant table will appear after the first response.</span></div>
        </div>
      </div>
      <div class="modal-footer">
        <a href="#" class="btn btn-light-primary" data-download-question-excel><i class="bi bi-file-earmark-excel me-2"></i>Download Excel</a>
        <button type="button" class="btn btn-danger" data-download-chart disabled><i class="bi bi-image me-2"></i>Download Chart Image</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
@vite('resources/js/app.js')
<script>
const liveQuestionChannels = @json($questions->pluck('module_id')->unique()->values());
document.addEventListener('DOMContentLoaded', () => {
  if (window.Echo) liveQuestionChannels.forEach(moduleId => window.Echo.channel(`live-questions.module.${moduleId}`)
    .listen('.live-question.changed', event => { if (event.action === 'answered') window.setTimeout(() => window.location.reload(), 300); }));
});
document.addEventListener('change', async (event) => {
  if (!event.target.matches('.live-toggle')) return;
  const toggle = event.target;
  toggle.disabled = true;
  try {
    const response = await fetch(toggle.dataset.url, {method:'POST', headers:{'X-CSRF-TOKEN':@json(csrf_token()), 'Accept':'application/json'}});
    if (!response.ok) throw new Error((await response.json()).message || 'Unable to update status');
    window.location.reload();
  } catch (error) { toggle.checked = !toggle.checked; toggle.disabled = false; toastr.error(error.message); }
});
document.addEventListener('click', async (event) => {
  const button = event.target.closest('.delete-question'); if (!button) return;
  const result = await Swal.fire({text:'Delete this live question?', icon:'warning', showCancelButton:true, confirmButtonText:'Delete'});
  if (!result.isConfirmed) return;
  const response = await fetch(button.dataset.url, {method:'DELETE', headers:{'X-CSRF-TOKEN':@json(csrf_token()), 'Accept':'application/json'}});
  if (response.ok) window.location.reload(); else toastr.error((await response.json()).message || 'Delete failed');
});

(() => {
  const modalElement = document.getElementById('questionAnalyticsModal');
  const modal = modalElement ? new bootstrap.Modal(modalElement) : null;
  const loading = modalElement?.querySelector('[data-analytics-loading]');
  const errorBox = modalElement?.querySelector('[data-analytics-error]');
  const content = modalElement?.querySelector('[data-analytics-content]');
  const empty = modalElement?.querySelector('[data-analytics-empty]');
  const results = modalElement?.querySelector('[data-analytics-results]');
  const chartElement = modalElement?.querySelector('[data-analytics-chart]');
  const responsesBody = modalElement?.querySelector('[data-analytics-responses]');
  const downloadChart = modalElement?.querySelector('[data-download-chart]');
  const downloadExcel = modalElement?.querySelector('[data-download-question-excel]');
  let analyticsChart = null;
  let activeQuestionId = null;

  const setText = (selector, value) => {
    const element = modalElement?.querySelector(selector);
    if (element) element.textContent = value == null ? '' : String(value);
  };

  const addCell = (row, value, className = '') => {
    const cell = document.createElement('td');
    cell.textContent = value == null ? '-' : String(value);
    if (className) cell.className = className;
    row.appendChild(cell);
  };

  const renderResponses = (responses) => {
    responsesBody.replaceChildren();
    responses.forEach(response => {
      const row = document.createElement('tr');
      addCell(row, response.user_name, 'fw-semibold text-gray-800');
      addCell(row, response.email);
      addCell(row, response.mobile);
      addCell(row, response.answer, 'fw-semibold');
      const resultCell = document.createElement('td');
      const badge = document.createElement('span');
      badge.textContent = response.result;
      badge.className = `badge ${response.result === 'Correct' ? 'badge-light-success' : (response.result === 'Incorrect' ? 'badge-light-danger' : 'badge-light-info')}`;
      resultCell.appendChild(badge);
      row.appendChild(resultCell);
      addCell(row, response.answered_at, 'analytics-date');
      responsesBody.appendChild(row);
    });
  };

  const renderChart = async (options) => {
    if (analyticsChart) await analyticsChart.destroy();
    chartElement.replaceChildren();
    analyticsChart = new ApexCharts(chartElement, {
      chart: {type: 'bar', height: 340, background: '#ffffff', toolbar: {show: false}, animations: {enabled: true}},
      series: [{name: 'Responses', data: options.map(option => Number(option.responses || 0))}],
      xaxis: {categories: options.map(option => String(option.label || '')), labels: {rotate: -25, trim: false, hideOverlappingLabels: false}},
      yaxis: {min: 0, forceNiceScale: true, title: {text: 'Response count'}, labels: {formatter: value => Number.isInteger(value) ? String(value) : ''}},
      plotOptions: {bar: {horizontal: false, columnWidth: '48%', borderRadius: 5, dataLabels: {position: 'top'}}},
      dataLabels: {enabled: true, offsetY: -20, style: {fontSize: '13px', colors: ['#181c32']}, formatter: value => String(value)},
      colors: ['#009ef7'],
      grid: {borderColor: '#eff2f5'},
      tooltip: {y: {formatter: value => `${value} response${value === 1 ? '' : 's'}`}},
      noData: {text: 'No responses received yet'},
    });
    await analyticsChart.render();
  };

  document.addEventListener('click', async event => {
    const button = event.target.closest('.view-question-chart');
    if (!button || !modal) return;

    loading.classList.remove('d-none');
    errorBox.classList.add('d-none');
    content.classList.add('d-none');
    empty.classList.add('d-none');
    results.classList.remove('d-none');
    downloadChart.disabled = true;
    downloadExcel.href = button.dataset.exportUrl;
    responsesBody.replaceChildren();
    modal.show();

    try {
      const response = await fetch(button.dataset.analyticsUrl, {headers: {'Accept': 'application/json'}});
      if (!response.ok) throw new Error((await response.json()).message || 'Unable to load question analytics.');
      const data = await response.json();
      activeQuestionId = data.question.id;
      setText('[data-analytics-question]', data.question.text);
      setText('[data-analytics-module]', data.question.module);
      setText('[data-analytics-type]', data.question.type);
      setText('[data-analytics-status]', data.question.status);
      setText('[data-analytics-total]', data.summary.total);
      setText('[data-analytics-correct]', data.summary.correct);
      setText('[data-analytics-incorrect]', data.summary.incorrect);
      modalElement.querySelectorAll('[data-quiz-summary]').forEach(element => element.classList.toggle('d-none', data.question.type !== 'Quiz'));
      loading.classList.add('d-none');
      content.classList.remove('d-none');

      if (!data.summary.total) {
        results.classList.add('d-none');
        empty.classList.remove('d-none');
        if (analyticsChart) { await analyticsChart.destroy(); analyticsChart = null; }
        return;
      }

      renderResponses(data.responses);
      await renderChart(data.options);
      downloadChart.disabled = false;
    } catch (error) {
      loading.classList.add('d-none');
      errorBox.textContent = error.message || 'Unable to load question analytics.';
      errorBox.classList.remove('d-none');
    }
  });

  downloadChart?.addEventListener('click', async () => {
    if (!analyticsChart || downloadChart.disabled) return;
    downloadChart.disabled = true;
    try {
      const image = await analyticsChart.dataURI({scale: 2});
      const link = document.createElement('a');
      link.href = image.imgURI;
      link.download = `question-${activeQuestionId || 'analytics'}-chart.png`;
      document.body.appendChild(link);
      link.click();
      link.remove();
    } catch (error) {
      toastr.error('Unable to download chart image.');
    } finally {
      downloadChart.disabled = false;
    }
  });

  modalElement?.addEventListener('hidden.bs.modal', async () => {
    if (analyticsChart) { await analyticsChart.destroy(); analyticsChart = null; }
    chartElement?.replaceChildren();
  });
})();
</script>
@endpush
