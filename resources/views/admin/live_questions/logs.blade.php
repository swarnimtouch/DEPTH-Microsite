@extends('layouts.admin')

@push('styles')
<style>
  .response-log-card .card-header{align-items:flex-start;gap:18px}
  .response-log-card .card-title{flex:1 1 720px}
  .response-log-card .card-toolbar{flex:0 0 auto}
  .response-log-table{width:100%!important;min-width:1450px;table-layout:fixed}
  .response-log-table th,.response-log-table td{vertical-align:top!important}
  .response-log-table .participant-cell{line-height:1.45}
  .response-log-table .content-cell{white-space:normal;overflow-wrap:anywhere;line-height:1.5}
  .response-log-table .answer-cell{white-space:normal;overflow-wrap:anywhere;color:#17213b}
  .response-log-table .date-cell{white-space:nowrap;line-height:1.5}
  @media(max-width:991.98px){.response-log-card .card-toolbar{width:100%;justify-content:flex-start}.response-log-card .card-title{flex-basis:100%}}
</style>
@endpush

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
  <div class="post d-flex flex-column-fluid"><div class="container-xxl">
    <div class="card response-log-card">
      <div class="card-header border-0 pt-6">
        <div class="card-title gap-3 flex-wrap">
          <input type="text" data-response-filter="search" class="form-control form-control-solid w-250px" placeholder="Search participant, question or answer">
          <select data-response-filter="module" class="form-select form-select-solid w-250px">
            <option value="">All Sessions</option>
            @foreach($modules as $module)<option value="{{ $module->id }}">{{ $module->title }}</option>@endforeach
          </select>
          <select data-response-filter="type" class="form-select form-select-solid w-175px">
            <option value="">Poll &amp; Quiz</option><option value="poll">Poll</option><option value="quiz">Quiz</option>
          </select>
        </div>
        <div class="card-toolbar">
          <a href="{{ route('admin.live_questions.logs.export') }}" class="btn btn-light-primary" id="exportResponseLogs"><i class="bi bi-file-earmark-excel me-2"></i>Export Excel</a>
        </div>
      </div>
      <div class="card-body pt-0"><div class="table-responsive">
        <table class="table align-middle table-row-dashed fs-6 gy-5 response-log-table" id="live_response_logs">
          <colgroup><col style="width:230px"><col style="width:285px"><col style="width:90px"><col style="width:360px"><col style="width:220px"><col style="width:105px"><col style="width:160px"></colgroup>
          <thead><tr class="text-muted fw-bolder fs-7 text-uppercase"><th>Participant</th><th>Session</th><th>Type</th><th>Question</th><th>Answer Given</th><th>Result</th><th>Answered At</th></tr></thead>
          <tbody></tbody>
        </table>
      </div></div>
    </div>
  </div></div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
<script>
"use strict";
KTUtil.onDOMContentLoaded(function () {
  const escapeHtml = value => $('<div>').text(value || '').html();
  const table = $('#live_response_logs').DataTable({
    processing: true, serverSide: true, searchDelay: 500, order: [],
    ajax: {
      url: @json(route('admin.live_questions.logs.datatable')),
      data: d => {
        d.search = $('[data-response-filter="search"]').val();
        d.module_id = $('[data-response-filter="module"]').val();
        d.type = $('[data-response-filter="type"]').val();
      }
    },
    columns: [
      {data: null, render: row => `<div class="d-flex flex-column participant-cell"><span class="fw-semibold text-gray-800">${escapeHtml(row.user_name)}</span><span class="text-muted fs-7 content-cell">${escapeHtml(row.email)}</span><span class="text-muted fs-8">${escapeHtml(row.mobile)}</span></div>`},
      {data: 'module', render: value => `<div class="content-cell">${escapeHtml(value)}</div>`},
      {data: 'type', render: value => `<span class="badge badge-light-primary">${escapeHtml(value)}</span>`},
      {data: 'question', render: value => `<div class="content-cell">${escapeHtml(value)}</div>`},
      {data: 'answer', render: value => `<strong class="answer-cell d-block">${escapeHtml(value)}</strong>`},
      {data: 'result', render: value => `<span class="badge ${value === 'Correct' ? 'badge-light-success' : (value === 'Incorrect' ? 'badge-light-danger' : 'badge-light-info')}">${escapeHtml(value)}</span>`},
      {data: 'answered_at', render: value => `<div class="date-cell">${escapeHtml(value)}</div>`}
    ]
  });

  function syncExport() {
    const url = new URL(@json(route('admin.live_questions.logs.export')), window.location.origin);
    const search = $('[data-response-filter="search"]').val();
    const moduleId = $('[data-response-filter="module"]').val();
    const type = $('[data-response-filter="type"]').val();
    if (search) url.searchParams.set('search', search);
    if (moduleId) url.searchParams.set('module_id', moduleId);
    if (type) url.searchParams.set('type', type);
    document.getElementById('exportResponseLogs').href = url.toString();
  }

  $('[data-response-filter]').on('keyup change', function () { syncExport(); table.draw(); });
  syncExport();
});
</script>
@endpush
