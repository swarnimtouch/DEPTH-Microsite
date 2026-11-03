@extends('layouts.admin')

@section('content')
<div class="content d-flex flex-column flex-column-fluid"><div class="post d-flex flex-column-fluid"><div class="container-xxl">
    <div class="card"><div class="card-header"><h3 class="card-title">Pre & Post Questions</h3><div class="card-toolbar"><a href="{{ route('admin.surveys.responses') }}" class="btn btn-light-primary me-2">View responses</a><a href="{{ route('admin.surveys.form') }}" class="btn btn-primary">Add question</a></div></div><div class="card-body">
        <form class="row g-3 mb-6" method="GET"><div class="col-md-4"><input name="search" value="{{ request('search') }}" class="form-control form-control-solid" placeholder="Question"></div><div class="col-md-4"><select name="module_id" class="form-select form-select-solid"><option value="">All chapters</option>@foreach($modules as $module)<option value="{{ $module->id }}" @selected(request('module_id') == $module->id)>{{ $module->title }}</option>@endforeach</select></div><div class="col-md-2"><select name="survey_type" class="form-select form-select-solid"><option value="">Both</option><option value="pre" @selected(request('survey_type') === 'pre')>Pre-event</option><option value="post" @selected(request('survey_type') === 'post')>Post-event</option></select></div><div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div></form>
        <div class="table-responsive"><table class="table align-middle table-row-dashed"><thead><tr><th>Chapter</th><th>Survey</th><th>Order</th><th>Question / Options</th><th>Status</th><th>Actions</th></tr></thead><tbody>
        @forelse($questions as $question)
            <tr><td>{{ $question->module?->title }}</td><td><span class="badge badge-light-{{ $question->survey_type === 'pre' ? 'danger' : 'success' }}">{{ ucfirst($question->survey_type) }}-event</span></td><td>{{ $question->sort_order }}</td><td><strong>{{ $question->question }}</strong><ol class="mb-0 mt-2">@foreach($question->options as $option)<li>{{ $option->option_text }}</li>@endforeach</ol></td><td><div class="form-check form-switch"><input class="form-check-input question-status" type="checkbox" data-id="{{ $question->id }}" {{ $question->status === 'active' ? 'checked' : '' }}></div></td><td class="text-nowrap"><a class="btn btn-sm btn-light-primary" href="{{ route('admin.surveys.form', $question->id) }}">Edit</a><button class="btn btn-sm btn-light-danger delete-question" data-id="{{ $question->id }}">Delete</button></td></tr>
        @empty<tr><td colspan="6" class="text-center text-muted py-8">No survey questions found.</td></tr>@endforelse
        </tbody></table></div>{{ $questions->links() }}
    </div></div>
</div></div></div>
@endsection

@push('scripts')
<script>
document.addEventListener('change', async (event) => {
    const input = event.target;
    if (input.classList.contains('question-status')) {
        const url = @json(route('admin.surveys.question.toggle', ['surveyQuestion' => '__ID__'])).replace('__ID__', input.dataset.id);
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
            });
            if (!response.ok) throw new Error('Update failed');
            toastr.success('Question status updated');
        } catch (e) {
            input.checked = !input.checked;
            toastr.error('Update failed');
        }
    }
});
document.addEventListener('click', (event) => {
    const button = event.target.closest('.delete-question'); if (!button) return;
    Swal.fire({text:'Delete this survey question?', icon:'warning', showCancelButton:true, confirmButtonText:'Delete'}).then(async result => {
        if (!result.isConfirmed) return;
        const url = @json(route('admin.surveys.question.delete', ['surveyQuestion' => '__ID__'])).replace('__ID__', button.dataset.id);
        try {
            const response = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
            });
            if (!response.ok) throw new Error('Delete failed');
            button.closest('tr').remove();
            toastr.success('Question deleted');
        } catch(e) {
            toastr.error('Delete failed');
        }
    });
});
</script>
@endpush
