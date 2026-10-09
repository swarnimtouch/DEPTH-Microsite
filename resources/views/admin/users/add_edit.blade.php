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
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
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
                                  action="{{ isset($user) ? route('admin.user.update', $user->id) : route('admin.user.store') }}"
                                  id="kt_user_form"
                                  enctype="multipart/form-data">
                                @csrf
                                @if(isset($user))
                                    @method('PUT')
                                @endif

                                <input type="hidden" name="avatar_removed" id="avatar_removed" value="0">
                                <input type="hidden" name="has_existing_avatar" id="has_existing_avatar"
                                       value="{{ isset($user) && $user->profile_image ? '1' : '0' }}">

                                <div class="card-body border-top p-9">
                                    @foreach($activeFields as $field)
                                        @php
                                            $fieldName = $field->field_name;
                                            $label = $field->label;
                                            $isRequired = $field->is_required ? 'required' : '';

                                            // Field name mapping for database columns
                                            $fieldMapping = [
                                                'mobile_number' => 'mobile',
                                                'alternative_mobile_number' => 'alternative_mobile',
                                            ];

                                            $dbFieldName = $fieldMapping[$fieldName] ?? $fieldName;
                                            $value = old($dbFieldName, $user->$dbFieldName ?? '');
                                        @endphp

                                        @if($fieldName == 'avatar')
                                            <!-- Avatar Upload -->
                                            <div class="row mb-6" id="avatarUploadSection">
                                                <label
                                                    class="col-lg-4 col-form-label {{ $isRequired }} fw-bold fs-6">{{ $label }}</label>
                                                <div class="col-lg-8">
                                                    <div class="image-input image-input-outline"
                                                         data-kt-image-input="true"
                                                         style="background-image: url('{{ asset('assets/media/avatars/blank.png') }}')">

                                                        <div class="image-input-wrapper w-125px h-125px"
                                                             id="avatarPreview"
                                                             style="background-image: url('{{ $user->profile_image?? asset('assets/media/avatars/blank.png') }}')">
                                                        </div>

                                                        <label
                                                            class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                                            data-kt-image-input-action="change" data-bs-toggle="tooltip"
                                                            title="Change avatar">
                                                            <i class="bi bi-pencil-fill fs-7"></i>
                                                            <input type="file" name="avatar" id="avatar"
                                                                   accept="image/*"/>
                                                            <input type="hidden" name="avatar_remove"/>
                                                        </label>

                                                        <span
                                                            class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                                            data-kt-image-input-action="cancel" data-bs-toggle="tooltip"
                                                            title="Cancel">
                                                        <i class="bi bi-x fs-2"></i>
                                                    </span>

                                                        <span
                                                            class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                                            data-kt-image-input-action="remove" data-bs-toggle="tooltip"
                                                            title="Remove">
                                                        <i class="bi bi-x fs-2"></i>
                                                    </span>
                                                    </div>

                                                    <div class="form-text">Allowed file types: jpg, jpeg, png, gif. Max
                                                        size: 5MB
                                                    </div>
                                                </div>
                                            </div>

                                        @elseif($fieldName == 'email')
                                            <!-- Email Field -->
                                            <div class="row mb-6">
                                                <label
                                                    class="col-lg-4 col-form-label {{ $isRequired }} fw-bold fs-6">{{ $label }}</label>
                                                <div class="col-lg-8">
                                                    <input type="email"
                                                           name="email"
                                                           value="{{ $value }}"
                                                           class="form-control form-control-lg form-control-solid"
                                                           placeholder="Enter email address"/>
                                                </div>
                                            </div>

                                        @elseif(in_array($fieldName, ['mobile_number', 'alternative_mobile_number']))
                                            <!-- Mobile Number Fields -->
                                            <div class="row mb-6">
                                                <label
                                                    class="col-lg-4 col-form-label {{ $isRequired }} fw-bold fs-6">{{ $label }}</label>
                                                <div class="col-lg-8">
                                                    <input type="tel"
                                                           name="{{ $dbFieldName }}"
                                                           value="{{ $value }}"
                                                           class="form-control form-control-lg form-control-solid"
                                                           placeholder="Enter mobile number"/>
                                                </div>
                                            </div>

                                        @elseif($fieldName == 'password')
                                            <!-- Password Field -->
                                            <div class="row mb-6">
                                                <label
                                                    class="col-lg-4 col-form-label {{ isset($user) ? '' : $isRequired }} fw-bold fs-6">
                                                    {{ $label }} {{ isset($user) ? '(Leave blank to keep current)' : '' }}
                                                </label>
                                                <div class="col-lg-8">
                                                    <input type="password"
                                                           name="password"
                                                           class="form-control form-control-lg form-control-solid"
                                                           placeholder="Enter password"
                                                           autocomplete="new-password"/>
                                                </div>
                                            </div>

                                        @elseif($fieldName == 'address')
                                            <!-- Address Textarea -->
                                            <div class="row mb-6">
                                                <label
                                                    class="col-lg-4 col-form-label {{ $isRequired }} fw-bold fs-6">{{ $label }}</label>
                                                <div class="col-lg-8">
                                                <textarea name="address"
                                                          class="form-control form-control-lg form-control-solid"
                                                          rows="3"
                                                          placeholder="Enter address">{{ $value }}</textarea>
                                                </div>
                                            </div>

                                        @elseif($fieldName == 'country')
                                            <!-- Country Dropdown -->
                                            <div class="row mb-6">
                                                <label
                                                    class="col-lg-4 col-form-label {{ $isRequired }} fw-bold fs-6">{{ $label }}</label>
                                                <div class="col-lg-8">
                                                    <select name="country"
                                                            class="form-select form-select-lg form-select-solid">
                                                        <option value="">Select Country</option>
                                                        <option value="India" {{ $value == 'India' ? 'selected' : '' }}>
                                                            India
                                                        </option>
                                                        <option value="USA" {{ $value == 'USA' ? 'selected' : '' }}>
                                                            USA
                                                        </option>
                                                        <option value="UK" {{ $value == 'UK' ? 'selected' : '' }}>UK
                                                        </option>
                                                        <option
                                                            value="Canada" {{ $value == 'Canada' ? 'selected' : '' }}>
                                                            Canada
                                                        </option>
                                                        <option
                                                            value="Australia" {{ $value == 'Australia' ? 'selected' : '' }}>
                                                            Australia
                                                        </option>
                                                        <!-- Add more countries as needed -->
                                                    </select>
                                                </div>
                                            </div>

                                        @else
                                            <!-- Default Text Input (includes first_name, last_name, etc.) -->
                                            <div class="row mb-6">
                                                <label
                                                    class="col-lg-4 col-form-label {{ $isRequired }} fw-bold fs-6">{{ $label }}</label>
                                                <div class="col-lg-8">
                                                    <input type="text"
                                                           name="{{ $dbFieldName }}"
                                                           value="{{ $value }}"
                                                           class="form-control form-control-lg form-control-solid"
                                                           placeholder="Enter {{ strtolower($label) }}"/>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>

                                <div class="card-footer d-flex justify-content-end py-6 px-9">
                                    <a href="{{ route('admin.user.index') }}"
                                       class="btn btn-light btn-active-light-primary me-2">Cancel</a>
                                    <button type="submit" class="btn btn-primary" id="kt_user_submit">
                                        <span class="indicator-label">{{ isset($user) ? 'Update' : 'Save' }}</span>
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
            const form = document.querySelector('#kt_user_form');
            const submitBtn = document.querySelector('#kt_user_submit');
            const avatarInput = document.querySelector('#avatar');
            const avatarRemovedInput = document.querySelector('#avatar_removed');
            const hasExistingAvatar = document.querySelector('#has_existing_avatar').value === '1';
            const isEditMode = {{ isset($user) ? 'true' : 'false' }};

            let avatarWasRemoved = false;
            let newAvatarSelected = false;

            // Avatar Remove
            if (avatarRemovedInput) {
                document.querySelectorAll('[data-kt-image-input-action="remove"]').forEach(btn => {
                    btn.addEventListener('click', function () {
                        avatarWasRemoved = true;
                        newAvatarSelected = false;
                        avatarRemovedInput.value = '1';

                        setTimeout(() => {
                            if (validator) validator.revalidateField('avatar');
                        }, 100);
                    });
                });

                // Avatar Cancel
                document.querySelectorAll('[data-kt-image-input-action="cancel"]').forEach(btn => {
                    btn.addEventListener('click', function () {
                        avatarWasRemoved = false;
                        newAvatarSelected = false;
                        avatarRemovedInput.value = '0';

                        setTimeout(() => {
                            if (validator) validator.revalidateField('avatar');
                        }, 100);
                    });
                });

                // New Avatar Select
                if (avatarInput) {
                    avatarInput.addEventListener('change', function () {
                        if (this.files && this.files.length > 0) {
                            newAvatarSelected = true;
                            avatarWasRemoved = false;
                            avatarRemovedInput.value = '0';
                        }
                    });
                }
            }

            const validationFields = {};

            @foreach($activeFields as $field)
                @php
                    $fieldName = $field->field_name;
                    $fieldMapping = [
                        'mobile_number' => 'mobile',
                        'alternative_mobile_number' => 'alternative_mobile',
                    ];
                    $dbFieldName = $fieldMapping[$fieldName] ?? $fieldName;
                @endphp

                @if($fieldName == 'avatar')
                validationFields['avatar'] = {
                validators: {
                    @if($field->is_required)
                    callback: {
                        message: 'Avatar is required',
                        callback: function () {
                            if (isEditMode && hasExistingAvatar) {
                                if (avatarWasRemoved && !newAvatarSelected) {
                                    return false;
                                }
                                if (!avatarWasRemoved) {
                                    return true;
                                }
                            }

                            if (!avatarInput.files || avatarInput.files.length === 0) {
                                return false;
                            }
                            return true;
                        }
                    },
                    @endif
                    file: {
                        extension: 'jpg,jpeg,png,gif',
                        type: 'image/jpeg,image/png,image/gif',
                        maxSize: 5242880,
                        message: 'Only JPG/PNG/GIF up to 5MB allowed'
                    }
                }
            };
            @elseif($fieldName == 'email')
                validationFields['email'] = {
                validators: {
                    @if($field->is_required)
                    notEmpty: {
                        message: 'Email is required'
                    },
                    @endif
                    emailAddress: {
                        message: 'Please enter a valid email address'
                    }
                }
            };
            @elseif($fieldName == 'password')
                validationFields['password'] = {
                validators: {
                    @if($field->is_required && !isset($user))
                    notEmpty: {
                        message: 'Password is required'
                    },
                    @endif
                        @if($field->is_required || isset($user))
                    callback: {
                        message: 'Password must be at least 6 characters',
                        callback: function (input) {
                            const value = input.value;
                            if (isEditMode && value === '') {
                                return true;
                            }
                            return value.length >= 6;
                        }
                    }
                    @endif
                }
            };
            @elseif(in_array($fieldName, ['mobile_number', 'alternative_mobile_number']))
                validationFields['{{ $dbFieldName }}'] = {
                validators: {
                    @if($field->is_required)
                    notEmpty: {
                        message: '{{ $field->label }} is required'
                    },
                    @endif
                    regexp: {
                        regexp: /^[0-9]{10}$/,
                        message: 'Please enter a valid 10-digit mobile number'
                    }
                }
            };
            @elseif($field->is_required)
                validationFields['{{ $dbFieldName }}'] = {
                validators: {
                    notEmpty: {
                        message: '{{ $field->label }} is required'
                    }
                }
            };
            @endif
            @endforeach

            // Form Validation
            const validator = FormValidation.formValidation(form, {
                fields: validationFields,
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
