@extends('layouts.website')

@section('body')
    @php
        $feedbackSubmitted = filled($progress->answers);
        $savedAnswers = collect($progress->answers ?? []);
    @endphp

    <div class="quiz-top-bar bg-white border-bottom py-3">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-8">
                    <span class="quiz-assessment-label d-block fw-bold text-primary-green mb-1">{{ strtoupper($module->title) }} FEEDBACK</span>
                    <h5 class="fw-bold text-dark-green mb-0" id="quizQuestionCounter">Question 1 of {{ $questions->count() }}</h5>
                </div>
            </div>
        </div>
    </div>

    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    @if($feedbackSubmitted)
                        <div class="alert alert-success">
                            Feedback submitted on {{ $progress->quiz_completed_at?->format('d M Y, h:i A') ?? 'this chapter' }}.
                        </div>
                    @endif

                    <form id="quizForm" method="POST" action="{{ route('modules.quiz.submit', $module->slug) }}" data-feedback-submitted="{{ $feedbackSubmitted ? '1' : '0' }}">
                        @csrf
                        @foreach($questions as $question)
                            <div class="quiz-question-step mb-5 {{ $loop->first ? '' : 'd-none' }}" data-step="{{ $loop->index }}">
                                <h4 class="quiz-question-text fw-bold text-dark-green mb-4">
                                    {{ $question->question }}
                                </h4>

                                @if(($question->question_type ?? 'options') === 'text')
                                    @php
                                        $savedText = data_get($savedAnswers->get((string) $question->id) ?? $savedAnswers->get($question->id), 'answer_text', '');
                                    @endphp
                                    <textarea
                                        name="answers[{{ $question->id }}]"
                                        rows="5"
                                        class="form-control form-control-lg"
                                        placeholder="Type your query"
                                        {{ $feedbackSubmitted ? 'readonly' : '' }}
                                    >{{ old("answers.{$question->id}", $savedText) }}</textarea>
                                    @if($feedbackSubmitted && filled(data_get($savedAnswers->get((string) $question->id) ?? $savedAnswers->get($question->id), 'admin_reply')))
                                        <div class="alert alert-info mt-4 mb-0">
                                            <div class="fw-bold mb-1">Admin Reply</div>
                                            <div>{{ data_get($savedAnswers->get((string) $question->id) ?? $savedAnswers->get($question->id), 'admin_reply') }}</div>
                                        </div>
                                    @endif
                                @else
                                    @php
                                        $savedOptionId = data_get($savedAnswers->get((string) $question->id) ?? $savedAnswers->get($question->id), 'selected_option_id', 0);
                                    @endphp
                                    @foreach($question->options as $option)
                                        @php $isSelected = old("answers.{$question->id}", $savedOptionId) == $option->id; @endphp
                                        <label class="quiz-option-card d-flex align-items-center w-100 mb-3 {{ $isSelected ? 'selected' : '' }}">
                                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}" class="d-none" {{ $isSelected ? 'checked' : '' }} {{ $feedbackSubmitted ? 'disabled' : '' }}>
                                            <span class="custom-radio-circle me-3"></span>
                                            <span class="option-text">{{ $option->option_text }}</span>
                                        </label>
                                    @endforeach
                                @endif
                            </div>
                        @endforeach

                        <div id="quizErrorMsg" class="text-danger small fw-semibold mt-3 d-none"></div>

                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-outline-purple px-4 py-2 fw-semibold d-none" id="prevQuestionBtn">Previous</button>
                            <span></span>
                            <button type="button" class="btn btn-primary-custom px-4 py-2 fw-semibold d-none" id="nextQuestionBtn">Next Question</button>
                            @if($feedbackSubmitted)
                                <a href="{{ route('modules.show', $module->slug) }}" class="btn btn-primary-custom px-4 py-2 fw-semibold d-none" id="submitQuizBtn">Back to Chapter</a>
                            @else
                                <button type="submit" class="btn btn-primary-custom px-4 py-2 fw-semibold d-none" id="submitQuizBtn">Submit Feedback</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('web/js/quiz.js') }}"></script>
@endpush
