@extends('layouts.admin')
@section('content')
<div class="content d-flex flex-column flex-column-fluid"><div class="post d-flex flex-column-fluid"><div class="container-xxl"><div class="card">
<div class="card-header"><h3 class="card-title">Pre & Post Survey Responses</h3><div class="card-toolbar"><a href="{{ route('admin.surveys.responses.export', request()->query()) }}" class="btn btn-primary">Export Excel</a></div></div>
<div class="card-body"><form class="row g-3 mb-6" method="GET"><div class="col-md-4"><input name="search" value="{{ request('search') }}" class="form-control form-control-solid" placeholder="Doctor, email or chapter"></div><div class="col-md-4"><select name="module_id" class="form-select form-select-solid"><option value="">All chapters</option>@foreach($modules as $module)<option value="{{ $module->id }}" @selected(request('module_id') == $module->id)>{{ $module->title }}</option>@endforeach</select></div><div class="col-md-2"><select name="survey_type" class="form-select form-select-solid"><option value="">Both</option><option value="pre" @selected(request('survey_type') === 'pre')>Pre-event</option><option value="post" @selected(request('survey_type') === 'post')>Post-event</option></select></div><div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div></form>
<div class="table-responsive"><table class="table align-middle table-row-dashed"><thead><tr><th>ID</th><th>Doctor</th><th>Chapter</th><th>Survey</th><th>Submitted</th><th>Submitted answers</th></tr></thead><tbody>
@forelse($submissions as $submission)<tr><td>{{ $submission->id }}</td><td><strong>{{ $submission->user?->name ?: 'Delegate' }}</strong><br><small>{{ $submission->user?->email }}</small></td><td>{{ $submission->module?->title }}</td><td><span class="badge badge-light-{{ $submission->survey_type === 'pre' ? 'danger' : 'success' }}">{{ ucfirst($submission->survey_type) }}-event</span></td><td>{{ $submission->submitted_at?->format('d M Y, h:i A') }}</td><td><button type="button" class="btn btn-sm btn-link text-danger response-answer-toggle p-0" aria-expanded="false">&#9654; View {{ $submission->answers->count() }} answers</button><ol class="mt-3 mb-0 d-none response-answer-list">@foreach($submission->answers as $answer)<li class="mb-2"><strong>{{ $answer->question_text }}</strong><br>{{ $answer->answer_text }}</li>@endforeach</ol></td></tr>@empty<tr><td colspan="6" class="text-center text-muted py-8">No responses found.</td></tr>@endforelse
</tbody></table></div>{{ $submissions->links() }}</div></div></div></div></div>
@endsection

@push('scripts')
<script>
document.addEventListener('click', event => {
    const button = event.target.closest('.response-answer-toggle');
    if (!button) return;
    const list = button.parentElement.querySelector('.response-answer-list');
    const opening = list.classList.contains('d-none');
    list.classList.toggle('d-none', !opening);
    button.setAttribute('aria-expanded', opening ? 'true' : 'false');
    button.innerHTML = `${opening ? '&#9660;' : '&#9654;'} View ${list.children.length} answers`;
});
</script>
@endpush
