@extends('layouts.admin')

@section('content')
@php
  $texts = old('options', $question->options?->pluck('option_text')->all() ?? []);
  $texts = array_pad($texts, max(4, count($texts)), '');
  $correctId = $question->options?->firstWhere('is_correct', true)?->id;
  $correctIndex = old('correct_option', $question->options?->values()->search(fn($option) => $option->id === $correctId));
@endphp
<div class="content d-flex flex-column flex-column-fluid"><div class="post d-flex flex-column-fluid"><div class="container-xxl">
  @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
  <div class="card"><div class="card-header"><h2 class="card-title">{{ $question->exists ? 'Edit' : 'Add' }} Live Question</h2></div>
    <form method="POST" action="{{ route('admin.live_questions.save', $question->id) }}">@csrf @if($question->exists) @method('PUT') @endif
      <div class="card-body p-9">
        <div class="row mb-6"><label class="col-lg-3 col-form-label required fw-bold">Live Session</label><div class="col-lg-9"><select name="module_id" class="form-select form-select-solid" required><option value="">Select session</option>@foreach($modules as $module)<option value="{{ $module->id }}" @selected(old('module_id', $question->module_id) == $module->id)>{{ $module->title }}</option>@endforeach</select></div></div>
        <div class="row mb-6"><label class="col-lg-3 col-form-label required fw-bold">Question Type</label><div class="col-lg-9"><select name="type" id="liveQuestionType" class="form-select form-select-solid"><option value="poll" @selected(old('type', $question->type) === 'poll')>Poll</option><option value="quiz" @selected(old('type', $question->type) === 'quiz')>Quiz</option></select><div class="form-text">Quiz shows correct/incorrect after answering. Poll shows voting results.</div></div></div>
        <div class="row mb-6"><label class="col-lg-3 col-form-label required fw-bold">Question</label><div class="col-lg-9"><textarea name="question" class="form-control form-control-solid" rows="3" required>{{ old('question', $question->question) }}</textarea></div></div>
        @foreach($texts as $index => $text)
        <div class="row mb-4"><label class="col-lg-3 col-form-label {{ $index < 2 ? 'required' : '' }} fw-bold">Option {{ $index + 1 }}</label><div class="col-lg-9"><div class="input-group"><span class="input-group-text quiz-correct"><input class="form-check-input" type="radio" name="correct_option" value="{{ $index }}" @checked((string)$correctIndex === (string)$index)></span><input name="options[{{ $index }}]" value="{{ $text }}" class="form-control form-control-solid" {{ $index < 2 ? 'required' : '' }}></div></div></div>
        @endforeach
        <div class="row"><div class="col-lg-9 offset-lg-3"><div id="correctHelp" class="alert alert-info py-3 mb-0">Select the radio beside the correct answer for a quiz.</div></div></div>
      </div>
      <div class="card-footer d-flex justify-content-end"><a href="{{ route('admin.live_questions') }}" class="btn btn-light me-2">Cancel</a><button class="btn btn-primary">Save Question</button></div>
    </form>
  </div>
</div></div></div>
@endsection
@push('scripts')
<script>
const typeSelect = document.getElementById('liveQuestionType');
function updateCorrectChoice(){ const quiz = typeSelect.value === 'quiz'; document.querySelectorAll('.quiz-correct').forEach(el => el.classList.toggle('d-none', !quiz)); document.getElementById('correctHelp').classList.toggle('d-none', !quiz); }
typeSelect.addEventListener('change', updateCorrectChoice); updateCorrectChoice();
</script>
@endpush
