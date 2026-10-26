@extends('layouts.admin')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
  <div class="post d-flex flex-column-fluid"><div class="container-xxl">
    <div class="card">
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
        <div class="card-toolbar gap-2">
          <a href="{{ route('admin.live_questions.logs.export') }}" class="btn btn-light-primary" id="exportResponseLogs"><i class="bi bi-file-earmark-excel me-2"></i>Export Excel</a>
          <a href="{{ route('admin.live_questions') }}" class="btn btn-light">Questions</a>
        </div>
      </div>
      <div class="card-body pt-0"><div class="table-responsive">
        <table class="table align-middle table-row-dashed fs-6 gy-5" id="live_response_logs">
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
      {data: null, render: row => `<div class="d-flex flex-column"><span class="fw-semibold text-gray-800">${escapeHtml(row.user_name)}</span><span class="text-muted fs-7">${escapeHtml(row.email)}</span><span class="text-muted fs-8">${escapeHtml(row.mobile)}</span></div>`},
      {data: 'module'},
      {data: 'type', render: value => `<span class="badge badge-light-primary">${escapeHtml(value)}</span>`},
      {data: 'question', render: value => `<div style="min-width:240px;max-width:420px">${escapeHtml(value)}</div>`},
      {data: 'answer', render: value => `<strong>${escapeHtml(value)}</strong>`},
      {data: 'result', render: value => `<span class="badge ${value === 'Correct' ? 'badge-light-success' : (value === 'Incorrect' ? 'badge-light-danger' : 'badge-light-info')}">${escapeHtml(value)}</span>`},
      {data: 'answered_at'}
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
