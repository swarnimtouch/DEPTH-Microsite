@extends('layouts.admin')

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
              <td>
                @if($canManageLiveQuestions)
                  <a href="{{ route('admin.live_questions.form', $question) }}" class="btn btn-sm btn-light-primary">Edit</a>
                  <button class="btn btn-sm btn-light-danger delete-question" data-url="{{ route('admin.live_questions.destroy', $question) }}">Delete</button>
                @else
                  <span class="text-muted">View only</span>
                @endif
              </td>
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
</script>
@endpush
