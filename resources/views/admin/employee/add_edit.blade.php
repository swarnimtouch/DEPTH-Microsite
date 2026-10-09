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
                                  action="{{ route('admin.employee.save',$employee->id??null) }}"
                                  id="kt_employee_form"
                                  enctype="multipart/form-data">
                                @csrf
                                @if(isset($employee))
                                    @method('PUT')
                                @endif


                                <div class="card-body border-top p-9">
                                    <div class="row mb-6">
                                        <label
                                            class="col-lg-4 col-form-label fw-bold fs-6"><span
                                                class="required">Zone</span> </label>
                                        <div class="col-lg-8">
                                            <select id="zone_id" name="zone_id"
                                                    class="form-control form-control-lg form-control-solid">
                                                <option value="">Select Zone</option>
                                                @foreach($zones as $key=>$value)
                                                    <option
                                                        value="{{$value->id}}" {{$employee->zone_id == $value->id ? 'selected':''}}>{{$value->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-6">
                                        <label
                                            class="col-lg-4 col-form-label fw-bold fs-6"><span
                                                class="required">Headquarter</span> </label>
                                        <div class="col-lg-8">
                                            <select id="headquarter_id" name="headquarter_id"
                                                    class="form-control form-control-lg form-control-solid">
                                                <option value="">Select Headquarter</option>
                                                @foreach($headquarters as $key=>$value)
                                                    <option
                                                        value="{{$value->id}}" {{$employee->headquarter_id == $value->id ? 'selected':''}}>{{$value->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-6">
                                        <label
                                            class="col-lg-4 col-form-label fw-bold fs-6"><span
                                                class="required">Region</span> </label>
                                        <div class="col-lg-8">
                                            <select id="region_id" name="region_id"
                                                    class="form-control form-control-lg form-control-solid">
                                                <option value="">Select Region</option>
                                                @foreach($regions as $key=>$value)
                                                    <option
                                                        value="{{$value->id}}" {{$employee->region_id == $value->id ? 'selected':''}}>{{$value->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-6">
                                        <label
                                            class="col-lg-4 col-form-label fw-bold fs-6"><span
                                                class="required">Area</span> </label>
                                        <div class="col-lg-8">
                                            <select id="area_id" name="area_id"
                                                    class="form-control form-control-lg form-control-solid">
                                                <option value="">Select Area</option>
                                                @foreach($areas as $key=>$value)
                                                    <option
                                                        value="{{$value->id}}" {{$employee->area_id == $value->id ? 'selected':''}}>{{$value->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-6">
                                        <label
                                            class="col-lg-4 col-form-label fw-bold fs-6"><span
                                                class="required">Division</span> </label>
                                        <div class="col-lg-8">
                                            <select id="division_id" name="division_id"
                                                    class="form-control form-control-lg form-control-solid">
                                                <option value="">Select Division</option>
                                                @foreach($divisions as $key=>$value)
                                                    <option
                                                        value="{{$value->id}}" {{$employee->division_id == $value->id ? 'selected':''}}>{{$value->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-6">
                                        <label
                                            class="col-lg-4 col-form-label fw-bold fs-6"><span
                                                class="required">Emp ID</span></label>
                                        <div class="col-lg-8">
                                            <input type="text"
                                                   name="emp_id"
                                                   value="{{ $employee->emp_id??'' }}"
                                                   class="form-control form-control-lg form-control-solid"
                                                   placeholder="Emp ID"/>
                                        </div>
                                    </div>
                                    <div class="row mb-6">
                                        <label
                                            class="col-lg-4 col-form-label fw-bold fs-6"><span
                                                class="required">Emp Chair ID</span></label>
                                        <div class="col-lg-8">
                                            <input type="text"
                                                   name="emp_chair_id"
                                                   value="{{ $employee->emp_chair_id??'' }}"
                                                   class="form-control form-control-lg form-control-solid"
                                                   placeholder="Emp Chair ID"/>
                                        </div>
                                    </div>
                                    <div class="row mb-6">
                                        <label
                                            class="col-lg-4 col-form-label fw-bold fs-6"><span
                                                class="required">Name</span></label>
                                        <div class="col-lg-8">
                                            <input type="text"
                                                   name="name"
                                                   value="{{ $employee->name??'' }}"
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
                                                   value="{{ $employee->email??'' }}"
                                                   class="form-control form-control-lg form-control-solid"
                                                   placeholder="Email"/>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer d-flex justify-content-end py-6 px-9">
                                    <a href="{{ route('admin.employee') }}"
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
            const form = document.querySelector('#kt_employee_form');
            const submitBtn = document.querySelector('#kt_submit');
            const zone = document.querySelector('#zone_id');
            const headquarter = document.querySelector('#headquarter_id');
            const region = document.querySelector('#region_id');

            const validator = FormValidation.formValidation(form, {
                fields: {
                    zone_id: {validators: {notEmpty: {message: "Zone is required"}}},
                    headquarter_id: {validators: {notEmpty: {message: "Headquarter is required"}}},
                    region_id: {validators: {notEmpty: {message: "Region is required"}}},
                    area_id: {validators: {notEmpty: {message: "Area is required"}}},
                    emp_id: {validators: {notEmpty: {message: "Emp ID is required"}}},
                    emp_chair_id: {validators: {notEmpty: {message: "Emp Chair ID is required"}}},
                    name: {validators: {notEmpty: {message: "Name is required"}}},
                    email: {
                        validators: {
                            notEmpty: {
                                message: "Email is required"
                            },
                            emailAddress: {
                                message: "Enter valid email"
                            },
                            remote: {
                                url: "{{route('admin.check-email-exists')}}",
                                method: "POST",
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                data: function () {
                                    return {
                                        email: form.querySelector('[name="email"]').value,
                                        id: {{$employee->id??0}}
                                    };
                                },
                                message: "Email already exists"
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
            zone.addEventListener('change', function (e) {
                e.preventDefault();
                $.ajax({
                    url: '{{ route("admin.area.get_headquarters") }}',
                    method: 'GET',
                    data: {
                        zone_id: zone.value,
                    },
                    success: function (response) {
                        const data = response.data;
                        var html = '<option value="">Select Headquarter</option>';
                        for (var i = 0; i < data.length; i++) {
                            html += '<option value="' + data[i].id + '">' + data[i].name + '</option>';
                        }
                        $("#headquarter_id").html(html);
                        $("#region_id").html('<option value="">Select Region</option>');
                        $("#area_id").html('<option value="">Select Area</option>');
                    },
                    error: function () {
                        Swal.fire({
                            text: "Error getting headquarters. Please try again.",
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "Ok",
                            customClass: {
                                confirmButton: "btn fw-bold btn-primary",
                            }
                        });
                    }
                });
            })
            headquarter.addEventListener('change', function (e) {
                e.preventDefault();
                $.ajax({
                    url: '{{ route("admin.area.get_regions") }}',
                    method: 'GET',
                    data: {
                        headquarter_id: headquarter.value,
                    },
                    success: function (response) {
                        const data = response.data;
                        var html = '<option value="">Select Region</option>';
                        for (var i = 0; i < data.length; i++) {
                            html += '<option value="' + data[i].id + '">' + data[i].name + '</option>';
                        }
                        $("#region_id").html(html);
                        $("#area_id").html('<option value="">Select Area</option>');
                    },
                    error: function () {
                        Swal.fire({
                            text: "Error getting region. Please try again.",
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "Ok",
                            customClass: {
                                confirmButton: "btn fw-bold btn-primary",
                            }
                        });
                    }
                });
            })
            region.addEventListener('change', function (e) {
                e.preventDefault();
                $.ajax({
                    url: '{{ route("admin.area.get_areas") }}',
                    method: 'GET',
                    data: {
                        region_id: region.value,
                    },
                    success: function (response) {
                        const data = response.data;
                        var html = '<option value="">Select Region</option>';
                        for (var i = 0; i < data.length; i++) {
                            html += '<option value="' + data[i].id + '">' + data[i].name + '</option>';
                        }
                        $("#area_id").html(html);
                    },
                    error: function () {
                        Swal.fire({
                            text: "Error getting region. Please try again.",
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "Ok",
                            customClass: {
                                confirmButton: "btn fw-bold btn-primary",
                            }
                        });
                    }
                });
            })
        });

    </script>
@endpush
