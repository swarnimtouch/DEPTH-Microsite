@extends('layouts.admin')

@section('content')
    @php
        $existingOptions = old('options') ?: $question->options->pluck('option_text')->toArray();
        $optionCount = max(5, count($existingOptions));
        $existingOptions = array_pad($existingOptions, $optionCount, '');
        $questionType = old('question_type', $question->question_type ?? 'options');
    @endphp
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="card">
                    <div class="card-header">
                        <div class="card-title fs-3 fw-bolder">{{ $question->id ? 'Edit Question' : 'Add Question' }}</div>
                    </div>

                    <form method="POST" action="{{ route('admin.quizzes.save', $question->id ?? null) }}" id="kt_quiz_form">
                        @csrf
                        @if($question->id)
                            @method('PUT')
                        @endif

                        <div class="card-body border-top p-9">
                            <div class="row mb-6">
                                <label class="col-lg-3 col-form-label fw-bold fs-6"><span class="required">Chapter</span></label>
                                <div class="col-lg-9">
                                    <select name="module_id" class="form-select form-select-lg form-select-solid">
                                        <option value="">Select Chapter</option>
                                        @foreach($modules as $module)
                                            <option value="{{ $module->id }}" {{ old('module_id', $question->module_id) == $module->id ? 'selected' : '' }}>{{ $module->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-6">
                                <label class="col-lg-3 col-form-label fw-bold fs-6"><span class="required">Question</span></label>
                                <div class="col-lg-9">
                                    <textarea name="question" rows="3" class="form-control form-control-lg form-control-solid" placeholder="Question">{{ old('question', $question->question) }}</textarea>
                                </div>
                            </div>

                            <div class="row mb-6">
                                <label class="col-lg-3 col-form-label fw-bold fs-6"><span class="required">Question Type</span></label>
                                <div class="col-lg-9">
                                    <select name="question_type" id="questionType" class="form-select form-select-lg form-select-solid">
                                        <option value="options" {{ $questionType === 'options' ? 'selected' : '' }}>Options</option>
                                        <option value="text" {{ $questionType === 'text' ? 'selected' : '' }}>Text Box</option>
                                    </select>
                                </div>
                            </div>

                            <div id="feedbackOptionsWrap">
                            <div class="row mb-2">
                                <label class="col-lg-3 col-form-label fw-bold fs-6">Feedback Options</label>
                                <div class="col-lg-9">
                                    <div class="text-muted fs-7">Use four options for impact questions or five options for 1-5 scale questions. Blank extra options will be ignored.</div>
                                </div>
                            </div>

                            @for($i = 0; $i < $optionCount; $i++)
                                <div class="row mb-6">
                                    <label class="col-lg-3 col-form-label fw-bold fs-6">
                                        @if($i < 2)
                                            <span class="required">Option {{ $i + 1 }}</span>
                                        @else
                                            Option {{ $i + 1 }}
                                        @endif
                                    </label>
                                    <div class="col-lg-9">
                                        <input type="text" name="options[{{ $i }}]" value="{{ $existingOptions[$i] }}" class="form-control form-control-lg form-control-solid" placeholder="Feedback option"/>
                                    </div>
                                </div>
                            @endfor
                            </div>

                            <div class="row mb-6">
                                <label class="col-lg-3 col-form-label fw-bold fs-6"><span class="required">Settings</span></label>
                                <div class="col-lg-4">
                                    <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $question->sort_order ?? 0) }}" class="form-control form-control-lg form-control-solid" placeholder="Sort order"/>
                                </div>
                                <div class="col-lg-5">
                                    <select name="status" class="form-select form-select-lg form-select-solid">
                                        <option value="active" {{ old('status', $question->status ?? 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status', $question->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer d-flex justify-content-end py-6 px-9">
                            <a href="{{ route('admin.quizzes') }}" class="btn btn-light btn-active-light-primary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary" id="kt_submit">
                                <span class="indicator-label">Save</span>
                                <span class="indicator-progress">Please wait...
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        "use strict";
        KTUtil.onDOMContentLoaded(function () {
            const form = document.querySelector('#kt_quiz_form');
            const submitBtn = document.querySelector('#kt_submit');
            const fields = {
                module_id: {validators: {notEmpty: {message: "Chapter is required"}}},
                question: {validators: {notEmpty: {message: "Question is required"}}},
                question_type: {validators: {notEmpty: {message: "Question type is required"}}},
                sort_order: {validators: {notEmpty: {message: "Sort order is required"}}},
                status: {validators: {notEmpty: {message: "Status is required"}}},
            };

            for (let i = 0; i < 2; i++) {
                fields[`options[${i}]`] = {validators: {notEmpty: {message: `Option ${i + 1} is required`}}};
            }

            const validator = FormValidation.formValidation(form, {
                fields,
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap: new FormValidation.plugins.Bootstrap5({rowSelector: '.row'})
                }
            });
            const questionType = document.querySelector('#questionType');
            const feedbackOptionsWrap = document.querySelector('#feedbackOptionsWrap');

            function toggleFeedbackOptions() {
                const isText = questionType?.value === 'text';
                feedbackOptionsWrap?.classList.toggle('d-none', isText);
                for (let i = 0; i < 2; i++) {
                    const fieldName = `options[${i}]`;
                    if (isText) {
                        validator.disableValidator(fieldName);
                    } else {
                        validator.enableValidator(fieldName);
                    }
                }
            }

            questionType?.addEventListener('change', toggleFeedbackOptions);
            toggleFeedbackOptions();

            submitBtn.addEventListener('click', function (e) {
                e.preventDefault();
                validator.validate().then(function (status) {
                    if (status === 'Valid') {
                        submitBtn.setAttribute('data-kt-indicator', 'on');
                        submitBtn.disabled = true;
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
