@extends('layouts.admin')
@section('content')
@php($optionsText = old('options_text', $question->exists ? $question->options->pluck('option_text')->implode("\n") : ''))
@php($matrixRowsText = old('matrix_rows_text', $question->exists ? $question->rows->pluck('row_text')->implode("\n") : ''))
<div class="content d-flex flex-column flex-column-fluid"><div class="post d-flex flex-column-fluid"><div class="container-xxl"><div class="card">
<div class="card-header"><h3 class="card-title">{{ $question->exists ? 'Edit' : 'Add' }} pre / post question</h3></div>
<form method="POST" action="{{ route('admin.surveys.save', $question->id) }}">@csrf @if($question->exists) @method('PUT') @endif
<div class="card-body p-9">
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="row g-5 mb-5"><div class="col-md-6"><label class="required form-label">Chapter</label><select name="module_id" class="form-select form-select-solid" required><option value="">Select chapter</option>@foreach($modules as $module)<option value="{{ $module->id }}" @selected(old('module_id', $question->module_id) == $module->id)>{{ $module->title }}</option>@endforeach</select></div><div class="col-md-3"><label class="required form-label">Survey</label><select name="survey_type" class="form-select form-select-solid"><option value="pre" @selected(old('survey_type', $question->survey_type) === 'pre')>Pre-event</option><option value="post" @selected(old('survey_type', $question->survey_type) === 'post')>Post-event</option></select></div><div class="col-md-3"><label class="required form-label">Question type</label><select name="question_type" id="surveyQuestionType" class="form-select form-select-solid"><option value="single_choice" @selected(old('question_type', $question->question_type) === 'single_choice')>Single choice</option><option value="matrix_single_choice" @selected(old('question_type', $question->question_type) === 'matrix_single_choice')>Table — one per row</option></select></div></div>
<div class="mb-5"><label class="required form-label">Question</label><textarea name="question" rows="3" class="form-control form-control-solid" required>{{ old('question', $question->question) }}</textarea></div>
<div class="mb-5 d-none" id="matrixRowsField"><label class="required form-label">Table rows — one per line</label><textarea name="matrix_rows_text" rows="6" class="form-control form-control-solid">{{ $matrixRowsText }}</textarea><div class="form-text">Participants must select one column answer for every row.</div></div>
<div class="mb-5"><label class="required form-label" id="surveyOptionsLabel">Options — one per line</label><textarea name="options_text" rows="7" class="form-control form-control-solid" required>{{ $optionsText }}</textarea><div class="form-text" id="surveyOptionsHelp">Participants select one answer per question.</div></div>
<div class="row g-5"><div class="col-md-6"><label class="required form-label">Display order</label><input type="number" min="0" name="sort_order" value="{{ old('sort_order', $question->sort_order ?? 0) }}" class="form-control form-control-solid" required></div><div class="col-md-6"><label class="required form-label">Status</label><select name="status" class="form-select form-select-solid"><option value="active" @selected(old('status', $question->status) === 'active')>Active</option><option value="inactive" @selected(old('status', $question->status) === 'inactive')>Inactive</option></select></div></div>
</div><div class="card-footer text-end"><a href="{{ route('admin.surveys') }}" class="btn btn-light me-2">Cancel</a><button class="btn btn-primary">Save question</button></div></form>
</div></div></div></div>
@endsection

@push('scripts')
<script>
function updateSurveyQuestionFields() {
    const isMatrix = document.getElementById('surveyQuestionType').value === 'matrix_single_choice';
    document.getElementById('matrixRowsField').classList.toggle('d-none', !isMatrix);
    document.getElementById('surveyOptionsLabel').textContent = isMatrix ? 'Table columns — one per line' : 'Options — one per line';
    document.getElementById('surveyOptionsHelp').textContent = isMatrix ? 'These choices appear as the table column headings.' : 'Participants select one answer per question.';
}
document.getElementById('surveyQuestionType')?.addEventListener('change', updateSurveyQuestionFields);
updateSurveyQuestionFields();
</script>
@endpush
