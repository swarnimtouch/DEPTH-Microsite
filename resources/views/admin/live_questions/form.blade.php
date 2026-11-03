@extends('layouts.admin')

@section('content')
@php
  $optionsText = old('options_text', $question->exists ? $question->options->pluck('option_text')->implode("\n") : '');
  $optionLines = collect(preg_split('/\r\n|\r|\n/', $optionsText))->map(fn($text) => trim($text))->filter()->values();
  $correctId = $question->options?->firstWhere('is_correct', true)?->id;
  $storedCorrectIndex = $question->options?->values()->search(fn($option) => $option->id === $correctId);
  $correctIndex = old('correct_option', $storedCorrectIndex === false ? null : $storedCorrectIndex);
@endphp
<div class="content d-flex flex-column flex-column-fluid"><div class="post d-flex flex-column-fluid"><div class="container-xxl">
  @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
  <div class="card"><div class="card-header"><h2 class="card-title">{{ $question->exists ? 'Edit' : 'Add' }} Live Question</h2></div>
    <form method="POST" action="{{ route('admin.live_questions.save', $question->id) }}">@csrf @if($question->exists) @method('PUT') @endif
      <div class="card-body p-9">
        <div class="row mb-6"><label class="col-lg-3 col-form-label required fw-bold">Live Session</label><div class="col-lg-9"><select name="module_id" class="form-select form-select-solid" required><option value="">Select session</option>@foreach($modules as $module)<option value="{{ $module->id }}" @selected(old('module_id', $question->module_id) == $module->id)>{{ $module->title }}</option>@endforeach</select></div></div>
        <div class="row mb-6"><label class="col-lg-3 col-form-label required fw-bold">Question Type</label><div class="col-lg-9"><select name="type" id="liveQuestionType" class="form-select form-select-solid"><option value="poll" @selected(old('type', $question->type) === 'poll')>Poll</option><option value="quiz" @selected(old('type', $question->type) === 'quiz')>Quiz</option></select><div class="form-text">Quiz shows correct/incorrect after answering. Poll shows voting results.</div></div></div>
        <div class="row mb-6"><label class="col-lg-3 col-form-label required fw-bold">Question</label><div class="col-lg-9"><textarea name="question" class="form-control form-control-solid" rows="3" required>{{ old('question', $question->question) }}</textarea></div></div>
        <div class="row mb-6"><label class="col-lg-3 col-form-label required fw-bold">Options</label><div class="col-lg-9"><textarea name="options_text" id="liveQuestionOptions" class="form-control form-control-solid" rows="7" required>{{ $optionsText }}</textarea><div class="form-text">Enter one option per line. Participants can select one answer.</div></div></div>
        <div class="row" id="correctAnswerRow"><label class="col-lg-3 col-form-label required fw-bold">Correct Answer</label><div class="col-lg-9"><select name="correct_option" id="correctOption" class="form-select form-select-solid"><option value="">Select correct answer</option>@foreach($optionLines as $index => $text)<option value="{{ $index }}" @selected((string)$correctIndex === (string)$index)>{{ $text }}</option>@endforeach</select><div class="form-text">Required only for a quiz.</div></div></div>
      </div>
      <div class="card-footer d-flex justify-content-end"><a href="{{ route('admin.live_questions') }}" class="btn btn-light me-2">Cancel</a><button class="btn btn-primary">Save Question</button></div>
    </form>
  </div>
</div></div></div>
@endsection
@push('scripts')
<script>
const typeSelect = document.getElementById('liveQuestionType');
const optionsText = document.getElementById('liveQuestionOptions');
const correctOption = document.getElementById('correctOption');
const correctAnswerRow = document.getElementById('correctAnswerRow');
function optionLines(){ return optionsText.value.split(/\r?\n/).map(value => value.trim()).filter(Boolean); }
function syncCorrectOptions(){
  const selected = correctOption.value;
  correctOption.innerHTML = '<option value="">Select correct answer</option>' + optionLines().map((text, index) => `<option value="${index}">${text.replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]))}</option>`).join('');
  if ([...correctOption.options].some(option => option.value === selected)) correctOption.value = selected;
}
function updateCorrectChoice(){
  const quiz = typeSelect.value === 'quiz';
  correctAnswerRow.classList.toggle('d-none', !quiz);
  correctOption.required = quiz;
}
typeSelect.addEventListener('change', updateCorrectChoice);
optionsText.addEventListener('input', syncCorrectOptions);
updateCorrectChoice();
</script>
@endpush
