@extends('layouts.admin')

@section('content')

    <div class="post d-flex flex-column-fluid" id="kt_post">

        <!--begin::Container-->
        <div id="kt_content_container" class="container-xxl">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <!--begin::Post-->
            <div class="card mb-5 mb-xl-10">
                <!--begin::Card header-->
                <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
                     data-bs-target="#kt_account_profile_details" aria-expanded="true"
                     aria-controls="kt_account_profile_details">
                    <!--begin::Card title-->
                    <div class="card-title m-0">
                        <h3 class="fw-bolder m-0">Change Password</h3>
                    </div>
                    <!--end::Card title-->
                </div>
                <!--begin::Card header-->
                <!--begin::Content-->
                <div id="kt_account_profile_details" class="collapse show">

                    <!--begin::Form-->
                    <form method="POST" action="{{ route('admin.password.update') }}"
                          id="kt_account_profile_details_form">

                        @csrf

                        <!--begin::Card body-->
                        <div class="card-body border-top p-9">
                            <!--begin::Input group-->
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label class="col-lg-4 col-form-label required fw-bold fs-6">Old Password</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-8">
                                    <!--begin::Row-->
                                    <div class="row">
                                        <!--begin::Col-->
                                        <div class="col-lg-12 fv-row">
                                            <input type="password" name="current_password" id="password"
                                                   class="form-control form-control-lg form-control-solid mb-3 mb-lg-0"
                                                   placeholder="Old Password"/>
                                        </div>
                                        <!--end::Col-->
                                        <!--begin::Col-->

                                        <!--end::Col-->
                                    </div>
                                    <!--end::Row-->
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label class="col-lg-4 col-form-label required fw-bold fs-6">New Password</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-8">
                                    <!--begin::Row-->
                                    <div class="row">
                                        <!--begin::Col-->
                                        <div class="col-lg-12 fv-row">
                                            <input type="password" name="password" id="password"
                                                   class="form-control form-control-lg form-control-solid mb-3 mb-lg-0"
                                                   placeholder="New Password"/>
                                        </div>
                                        <!--end::Col-->
                                        <!--begin::Col-->

                                        <!--end::Col-->
                                    </div>
                                    <!--end::Row-->
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label class="col-lg-4 col-form-label required fw-bold fs-6">Confirm New
                                    Password</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-8 fv-row">
                                    <input type="password" name="password_confirmation" id="password_confirmation"
                                           class="form-control form-control-lg form-control-solid"
                                           placeholder="Confirm Password"/>
                                </div>


                                <!--end::Col-->
                            </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->


                        </div>
                        <!--end::Card body-->
                        <!--begin::Actions-->
                        <div class="card-footer d-flex justify-content-end py-6 px-9">
                            <a href="{{ route('admin.dashboard') }}" type="reset"
                               class="btn btn-light btn-active-light-primary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary" id="kt_password_update_submit">Save</button>
                        </div>
                        <!--end::Actions-->
                    </form>
                    <!--end::Form-->
                </div>
                <!--end::Content-->
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!--begin::Page Custom Javascript(used by this page)-->
    <script src="{{ asset('assets/js/custom/widgets.js')}}"></script>
    <script>
        "use strict";

        var KTPasswordUpdate = function () {
            var form;
            var submitButton;
            var validator;

            return {
                init: function () {

                    form = document.querySelector("#kt_account_profile_details_form");
                    submitButton = document.querySelector("#kt_password_update_submit");

                    if (!form) return;

                    validator = FormValidation.formValidation(form, {
                        fields: {
                            current_password: {
                                validators: {
                                    notEmpty: {
                                        message: "Old password is required"
                                    }
                                }
                            },
                            password: {
                                validators: {
                                    notEmpty: {
                                        message: "New password is required"
                                    },
                                    stringLength: {
                                        min: 6,
                                        message: "Password must be at least 6 characters"
                                    }
                                }
                            },
                            password_confirmation: {
                                validators: {
                                    notEmpty: {
                                        message: "Confirm password is required"
                                    },
                                    identical: {
                                        compare: function () {
                                            return form.querySelector('[name="password"]').value;
                                        },
                                        message: "Passwords do not match"
                                    }
                                }
                            }
                        },
                        plugins: {
                            trigger: new FormValidation.plugins.Trigger(),
                            bootstrap: new FormValidation.plugins.Bootstrap5({
                                rowSelector: '.fv-row',
                                eleInvalidClass: '',
                                eleValidClass: ''
                            })
                        }
                    });

                    submitButton.addEventListener("click", function (e) {
                        e.preventDefault();

                        validator.validate().then(function (status) {
                            if (status === 'Valid') {

                                submitButton.setAttribute("data-kt-indicator", "on");
                                submitButton.disabled = true;

                                form.submit();
                            }
                        });
                    });
                }
            };
        }();

        KTUtil.onDOMContentLoaded(function () {
            KTPasswordUpdate.init();
        });

    </script>

@endpush
