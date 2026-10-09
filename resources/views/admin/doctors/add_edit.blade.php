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
                                  action="{{ route('admin.doctors.save',$doctor->id??null) }}"
                                  id="kt_doctor_form"
                                  enctype="multipart/form-data">
                                @csrf
                                @if(isset($doctor))
                                    @method('PUT')
                                @endif


                                <div class="card-body border-top p-9">
                                    <div class="row mb-6">
                                        <!--begin::Label-->
                                        <label class="col-lg-4 col-form-label fw-bold fs-6">Profile Image</label>
                                        <!--end::Label-->
                                        <!--begin::Col-->
                                        <div class="col-lg-8">
                                            <!--begin::Image input-->
                                            <div class="image-input image-input-outline" data-kt-image-input="true">
                                                <!--begin::Preview existing avatar-->
                                                <div class="image-input-wrapper w-125px h-125px"
                                                     style="background-image: url({{ $doctor->profile_image }})">
                                                </div>

                                                <!--end::Preview existing avatar-->
                                                <!--begin::Label-->
                                                <label
                                                    class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                                    data-kt-image-input-action="change" data-bs-toggle="tooltip"
                                                    title="Change Profile Image">
                                                    <i class="bi bi-pencil-fill fs-7"></i>
                                                    <!--begin::Inputs-->
                                                    <input type="file" name="profile_image" accept=".png, .jpg, .jpeg"/>
                                                    <input type="hidden" name="profile_image_remove"/>
                                                    <!--end::Inputs-->
                                                </label>
                                                <!--end::Label-->
                                                <!--begin::Cancel-->
                                                <span
                                                    class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                                    data-kt-image-input-action="cancel" data-bs-toggle="tooltip"
                                                    title="Cancel Profile Image">
																<i class="bi bi-x fs-2"></i>
															</span>
                                                <!--end::Cancel-->
                                                <!--begin::Remove-->
                                                <span
                                                    class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                                    data-kt-image-input-action="remove" data-bs-toggle="tooltip"
                                                    title="Remove Profile Image">
																<i class="bi bi-x fs-2"></i>
															</span>
                                                <!--end::Remove-->
                                            </div>
                                            <!--end::Image input-->
                                            <!--begin::Hint-->
                                            <div class="form-text">Allowed file types: png, jpg, jpeg.</div>
                                            <!--end::Hint-->
                                        </div>
                                        <!--end::Col-->
                                    </div>
                                    <div class="row mb-6">
                                        <label
                                            class="col-lg-4 col-form-label fw-bold fs-6"><span
                                                class="required">Name</span></label>
                                        <div class="col-lg-8">
                                            <input type="text"
                                                   name="name"
                                                   value="{{ $doctor->name??'' }}"
                                                   class="form-control form-control-lg form-control-solid"
                                                   placeholder="Name"/>
                                        </div>
                                    </div>
                                    <div class="row mb-6">
                                        <label
                                            class="col-lg-4 col-form-label fw-bold fs-6"><span
                                                class="required">Email</span></label>
                                        <div class="col-lg-8">
                                            <input type="email"
                                                   name="email"
                                                   value="{{ $doctor->email??'' }}"
                                                   class="form-control form-control-lg form-control-solid"
                                                   placeholder="Email"/>
                                        </div>
                                    </div>
                                    <div class="row mb-6">
                                        <label
                                            class="col-lg-4 col-form-label fw-bold fs-6"><span
                                                class="required">Mobile</span></label>
                                        <div class="col-lg-8">
                                            <input type="text"
                                                   name="mobile"
                                                   value="{{ $doctor->mobile??'' }}"
                                                   class="form-control form-control-lg form-control-solid"
                                                   placeholder="Mobile"/>
                                        </div>
                                    </div>
                                    <div class="row mb-6">
                                        <label
                                            class="col-lg-4 col-form-label fw-bold fs-6"><span
                                                class="required">Hospital</span></label>
                                        <div class="col-lg-8">
                                            <input type="text"
                                                   name="hospital"
                                                   value="{{ $doctor->hospital??'' }}"
                                                   class="form-control form-control-lg form-control-solid"
                                                   placeholder="Hospital"/>
                                        </div>
                                    </div>

                                    <div class="row mb-6">
                                        <label
                                            class="col-lg-4 col-form-label fw-bold fs-6"><span
                                                class="required">Country</span> </label>
                                        <div class="col-lg-8">
                                            <select id="country" name="country"
                                                    class="form-control form-control-lg form-control-solid">
                                                <option value="">Select Country</option>
                                                @foreach($country as $key=>$value)
                                                    <option
                                                        value="{{$value->name}}" {{$doctor->country == $value->name ? 'selected':''}}>{{$value->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer d-flex justify-content-end py-6 px-9">
                                    <a href="{{ route('admin.doctors') }}"
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

            const form = document.querySelector('#kt_doctor_form');
            const submitBtn = document.querySelector('#kt_submit');


            const validator = FormValidation.formValidation(form, {
                fields: {
                    name: {
                        validators: {
                            notEmpty: {message: "Name is required"}
                        }
                    },
                    email: {
                        validators: {
                            notEmpty: {message: "Email is required"},
                            emailAddress: {message: "Enter valid email"},
                            remote: {
                                url: "{{ route('admin.check-email-exists') }}",
                                method: "POST",
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                data: () => ({
                                    email: form.querySelector('[name="email"]').value,
                                    id: {{ $doctor->id ?? 0 }}
                                }),
                                message: "Email already exists"
                            }
                        }
                    },
                    mobile: {
                        validators: {
                            notEmpty: {message: "Mobile is required"},
                            remote: {
                                url: "{{ route('admin.check-mobile-exists') }}",
                                method: "POST",
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                data: () => ({
                                    mobile: form.querySelector('[name="mobile"]').value,
                                    id: {{ $doctor->id ?? 0 }}
                                }),
                                message: "Mobile already exists"
                            }
                        }
                    },
                    hospital: {
                        validators: {
                            notEmpty: {message: "Hospital is required"}
                        }
                    },
                    country: {
                        validators: {
                            notEmpty: {message: "Country is required"},
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

        const DEFAULT_AVATAR = "{{ asset('assets/media/avatars/blank.png') }}";

        document.addEventListener('DOMContentLoaded', function () {

            const imageInput = document.querySelector('[data-kt-image-input="true"]');
            if (!imageInput) return;

            const wrapper = imageInput.querySelector('.image-input-wrapper');

            imageInput.querySelector('[data-kt-image-input-action="cancel"]')
                .addEventListener('click', () => {
                    wrapper.style.backgroundImage = `url('${DEFAULT_AVATAR}')`;
                });

            imageInput.querySelector('[data-kt-image-input-action="remove"]')
                .addEventListener('click', () => {
                    wrapper.style.backgroundImage = `url('${DEFAULT_AVATAR}')`;
                });

        });
    </script>
@endpush
