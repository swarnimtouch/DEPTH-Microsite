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
                        <div class="card-title fs-3 fw-bolder">{{$title}}</div>
                    </div>
                    <!-- Create/Edit User Card -->
                    <div class="mb-5 mb-xl-10">
                        <div id="kt_user_wrapper" class="collapse show">
                            <form method="POST"
                                  action="{{ route('admin.faqs.save',$faq->id??null) }}"
                                  id="kt_faq_form"
                                  enctype="multipart/form-data">
                                @csrf
                                @if(isset($faq))
                                    @method('PUT')
                                @endif


                                <div class="card-body border-top p-9">

                                    <div class="row mb-6">
                                        <label
                                            class="col-lg-4 col-form-label fw-bold fs-6"><span
                                                class="required">Question</span></label>
                                        <div class="col-lg-8">
                                            <input type="text"
                                                   name="que"
                                                   value="{{ $faq->que??'' }}"
                                                   class="form-control form-control-lg form-control-solid"
                                                   placeholder="Question"/>
                                        </div>
                                    </div>
                                    <div class="row mb-6">
                                        <label
                                            class="col-lg-4 col-form-label fw-bold fs-6"><span
                                                class="required">Answer</span></label>
                                        <div class="col-lg-8">
                                            <textarea
                                                name="ans"
                                                class="form-control form-control-lg form-control-solid"
                                                placeholder="Answer">{{$faq->ans}}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer d-flex justify-content-end py-6 px-9">
                                    <a href="{{ route('admin.faqs') }}"
                                       class="btn btn-light btn-active-light-primary me-2">Cancel</a>
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
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        "use strict";

        KTUtil.onDOMContentLoaded(function () {

            const form = document.querySelector('#kt_faq_form');
            const submitBtn = document.querySelector('#kt_submit');

            const validator = FormValidation.formValidation(form, {
                fields: {
                    que: {
                        validators: {
                            notEmpty: {
                                message: "Question is required"
                            }
                        }
                    },
                    ans: {
                        validators: {
                            notEmpty: {
                                message: "Answer is required"
                            }
                        }
                    }
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: '.row'
                    })
                }
            });

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
