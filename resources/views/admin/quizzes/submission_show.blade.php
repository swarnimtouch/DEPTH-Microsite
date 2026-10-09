@extends('layouts.admin')

@section('content')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="card mb-6">
                    <div class="card-body d-flex flex-column flex-md-row justify-content-between gap-4">
                        <div>
                            <div class="text-muted fw-semibold mb-1">Doctor</div>
                            <h4 class="mb-1">{{ $progress->user?->name ?: 'Delegate' }}</h4>
                            <div class="text-muted">{{ $progress->user?->email ?: '-' }} @if($progress->user?->mobile) · {{ $progress->user->mobile }} @endif</div>
                        </div>
                        <div>
                            <div class="text-muted fw-semibold mb-1">Chapter</div>
                            <h5 class="mb-1">{{ $progress->module?->title ?: '-' }}</h5>
                            <div class="text-muted">Submitted {{ $progress->quiz_completed_at?->format('d M Y, h:i A') ?: '-' }}</div>
                        </div>
                        <div class="align-self-md-center">
                            <a href="{{ route('admin.quizzes.submissions') }}" class="btn btn-light">Back</a>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.quizzes.submissions.reply', $progress->id) }}">
                    @csrf
                    <div class="card">
                        <div class="card-header border-0 pt-6">
                            <div class="card-title">
                                <h3 class="fw-bold mb-0">Feedback Answers</h3>
                            </div>
                            <div class="card-toolbar">
                                <button type="submit" class="btn btn-primary">Save Replies</button>
                            </div>
                        </div>
                        <div class="card-body">
                            @foreach($progress->module->questions as $question)
                                @php
                                    $answer = $answers->get((string) $question->id) ?? $answers->get($question->id) ?? [];
                                    $answerText = $answer['answer_text'] ?? null;
                                    $selectedOptionId = $answer['selected_option_id'] ?? null;
                                    $selectedOption = $selectedOptionId ? $question->options->firstWhere('id', (int) $selectedOptionId) : null;
                                    $isTextQuestion = ($question->question_type ?? 'options') === 'text';
                                @endphp
                                <div class="border rounded p-5 mb-5">
                                    <div class="d-flex justify-content-between gap-3 mb-3">
                                        <h5 class="fw-bold text-gray-900 mb-0">{{ $loop->iteration }}. {{ $question->question }}</h5>
                                        <span class="badge {{ $isTextQuestion ? 'badge-light-info' : 'badge-light-primary' }}">{{ $isTextQuestion ? 'Text' : 'Options' }}</span>
                                    </div>

                                    <div class="bg-light rounded p-4 mb-4">
                                        <div class="text-muted fw-semibold mb-1">User Answer</div>
                                        @if($isTextQuestion)
                                            <div class="text-gray-800">{{ $answerText ?: 'No answer submitted.' }}</div>
                                        @else
                                            <div class="text-gray-800">{{ $selectedOption?->option_text ?: 'No option selected.' }}</div>
                                        @endif
                                    </div>

                                    @if($isTextQuestion && filled($answerText))
                                        <label class="form-label fw-semibold">Admin Reply</label>
                                        <textarea
                                            name="replies[{{ $question->id }}]"
                                            class="form-control form-control-solid"
                                            rows="4"
                                            maxlength="2000"
                                            placeholder="Write a reply for this question"
                                        >{{ old("replies.{$question->id}", $answer['admin_reply'] ?? '') }}</textarea>
                                        @if(!empty($answer['admin_replied_at']))
                                            <div class="text-muted fs-8 mt-2">Last replied {{ \Carbon\Carbon::parse($answer['admin_replied_at'])->format('d M Y, h:i A') }}</div>
                                        @endif
                                    @elseif($isTextQuestion)
                                        <div class="text-muted">Reply is available after the user submits a text answer.</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <div class="card-footer d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Save Replies</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
