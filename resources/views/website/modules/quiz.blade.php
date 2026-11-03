@extends('layouts.website')
@section('standalone_page', 'true')

@push('styles')
    <link rel="stylesheet" href="{{ asset('web/depth/style.css') }}?v={{ filemtime(public_path('web/depth/style.css')) }}">
    <style>
        .logo-hetero{height:48px;width:auto}.brand-divider{width:1px;height:46px;background:#d9dee7}.logo-depth-brand{height:48px;width:auto;max-width:250px}
        .depth-quiz-main{min-height:560px;background:#f7f9fc;padding-bottom:64px}
        .depth-quiz-main .quiz-top-bar{padding-top:30px!important;padding-bottom:24px!important;background:#fff!important}
        .depth-quiz-main .quiz-top-bar .container,.depth-quiz-main>section>.container{max-width:1040px}
        .depth-quiz-main .quiz-assessment-label{color:#d71920!important;font-size:12px;letter-spacing:.08em}
        .depth-quiz-main .quiz-question-text{color:#1f2a44!important}
        .depth-quiz-main .btn-primary-custom{background:#d71920;border-color:#d71920;color:#fff}
        .depth-quiz-main .btn-primary-custom:hover{background:#b8141b;border-color:#b8141b;color:#fff}
        .feedback-card{background:#fff;border:1px solid #dce2ea;border-radius:16px;padding:32px;box-shadow:0 14px 36px rgba(31,42,68,.07)}
        .feedback-card .alert-success{border:1px solid #b7e0c9;background:#edf9f2;color:#237a4b;border-radius:10px;padding:14px 16px}
        .quiz-option-card{padding:16px 18px;border:1px solid #d9dee7;border-radius:11px;background:#fff;color:#1f2a44;cursor:pointer;transition:border-color .2s,background .2s,box-shadow .2s}
        .quiz-option-card:hover{border-color:#d71920;box-shadow:0 5px 15px rgba(31,42,68,.05)}
        .quiz-option-card.selected{border-color:#d71920;background:#fff6f6}
        .custom-radio-circle{width:19px;height:19px;border:1px solid #cbd3df;border-radius:50%;position:relative;flex:0 0 auto}
        .quiz-option-card.selected .custom-radio-circle{border-color:#d71920}.quiz-option-card.selected .custom-radio-circle::after{content:"";position:absolute;inset:4px;border-radius:50%;background:#d71920}
        .option-text{font-size:15px;font-weight:600}.feedback-card textarea{border:1px solid #d9dee7;border-radius:11px;padding:16px;resize:vertical}.feedback-card textarea:focus{border-color:#d71920;box-shadow:0 0 0 3px rgba(215,25,32,.1)}
        .btn-outline-purple{border:1px solid #cbd3df!important;color:#1f2a44!important;background:#fff!important}.btn-outline-purple:hover{border-color:#d71920!important;color:#d71920!important}
        @media(max-width:760px){.logo-hetero{height:36px}.brand-divider{height:34px}.logo-depth-brand{height:34px;max-width:min(36vw,150px)}.feedback-card{padding:22px 18px}.depth-quiz-main .quiz-top-bar{padding-top:22px!important}}
    </style>
@endpush

@section('body')
    @php
        $feedbackSubmitted = filled($progress->answers);
        $savedAnswers = collect($progress->answers ?? []);
        $dashboardSection = 'live';
    @endphp

    <div class="page">
    @include('partials.website.depth_dashboard_header')
    <main class="depth-quiz-main">

    <div class="quiz-top-bar bg-white border-bottom py-3">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-12">
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
                    <div class="feedback-card">
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
                                <a href="{{ route('modules.show', $module->slug) }}" class="btn btn-primary-custom px-4 py-2 fw-semibold d-none" id="submitQuizBtn">Go to Webinar</a>
                            @else
                                <button type="submit" class="btn btn-primary-custom px-4 py-2 fw-semibold d-none" id="submitQuizBtn">Submit Feedback</button>
                            @endif
                        </div>
                    </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    </main>
    @include('partials.website.depth_dashboard_footer')
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('web/js/quiz.js') }}"></script>
@endpush
