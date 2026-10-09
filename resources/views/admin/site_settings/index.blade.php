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
                    <div class="mb-5 mb-xl-10">


                        <div id="kt_account_profile_details" class="collapse show">
                            <form method="POST" action="{{ route('admin.settings.update') }}"
                                  id="kt_settings_form" enctype="multipart/form-data">
                                @csrf

                                <div class="card-body border-top p-9">
                                    @foreach($fields as $field)
                                        <div class="row mb-6">
                                            <label class="col-lg-4 col-form-label fw-bold fs-6
                                        {{ !empty($field['extra']) && strpos($field['extra'], '"required":"required"') !== false ? 'required' : '' }}">
                                                {{ $field['label'] }}
                                            </label>

                                            <div class="col-lg-8">
                                                <div class="row">
                                                    <div class="col-lg-12 fv-row">
                                                        @php
                                                            $type = $field['type'];
                                                            $extraArray = !empty($field['extra']) ? json_decode($field['extra'], true) : [];
                                                            $isRequired = isset($extraArray['required']) && $extraArray['required'] === 'required';

                                                            $optionsArray = [];
                                                            if (!empty($field['options'])) {
                                                                // Check if options is already JSON
                                                                if (is_string($field['options'])) {
                                                                    $decoded = json_decode($field['options'], true);
                                                                    if (json_last_error() === JSON_ERROR_NONE) {
                                                                        $optionsArray = $decoded;
                                                                    } else {
                                                                        $items = explode(',', $field['options']);
                                                                        foreach ($items as $item) {
                                                                            $item = trim($item);
                                                                            $optionsArray[$item] = ucfirst($item);
                                                                        }
                                                                    }
                                                                } else {
                                                                    $optionsArray = $field['options'];
                                                                }
                                                            }
                                                        @endphp

                                                        @if(in_array($type, ['text','number','email','url']))
                                                            <input type="{{ $type }}"
                                                                   name="{{ $field['unique_name'] }}"
                                                                   id="{{ $field['unique_name'] }}"
                                                                   class="form-control form-control-lg form-control-solid"
                                                                   placeholder="{{ $field['label'] }}"
                                                                   value="{{ old($field['unique_name'], $field['value']) }}"
                                                            {{ $isRequired ? 'required' : '' }}
                                                            @if(!empty($extraArray))
                                                                @foreach($extraArray as $k => $v)
                                                                    @if($k !== 'required')
                                                                        {{ $k }}="{{ $v }}"
                                                                    @endif
                                                                @endforeach
                                                            @endif
                                                            >

                                                        @elseif($type === 'textarea')
                                                            <textarea name="{{ $field['unique_name'] }}"
                                                                      id="{{ $field['unique_name'] }}"
                                                                      class="form-control form-control-lg form-control-solid"
                                                                      placeholder="{{ $field['label'] }}"
                                                                      rows="4"
                                                            {{ $isRequired ? 'required' : '' }}
                                                            @if(!empty($extraArray))
                                                                @foreach($extraArray as $k => $v)
                                                                    @if($k !== 'required')
                                                                        {{ $k }}="{{ $v }}"
                                                                    @endif
                                                                @endforeach
                                                            @endif
                                                            >{{ old($field['unique_name'], $field['value']) }}</textarea>

                                                            {{-- FILE --}}
                                                        @elseif($type === 'file')
                                                            <input type="file"
                                                                   name="{{ $field['unique_name'] }}"
                                                                   id="{{ $field['unique_name'] }}"
                                                                   class="form-control form-control-lg form-control-solid"
                                                            {{ $isRequired && empty($field['value']) ? 'required' : '' }}
                                                            @if(!empty($extraArray))
                                                                @foreach($extraArray as $k => $v)
                                                                    @if($k !== 'required')
                                                                        {{ $k }}="{{ $v }}"
                                                                    @endif
                                                                @endforeach
                                                            @endif
                                                            >
                                                            @if(!empty($field['value']))
                                                                <div class="mt-3">
                                                                    <img
                                                                        src="{{ siteSettingAsset($field['unique_name'], 'web/depth/assets/hetero.png') }}"
                                                                        class="img-fluid"
                                                                        style="max-height:60px">
                                                                </div>
                                                            @endif

                                                            {{-- RADIO BUTTON --}}
                                                        @elseif($type === 'radio')
                                                            <div class="radio-group">
                                                                @if(!empty($optionsArray))
                                                                    @foreach($optionsArray as $optionValue => $optionLabel)
                                                                        <div
                                                                            class="form-check form-check-custom form-check-solid mb-3">
                                                                            <input class="form-check-input"
                                                                                   type="radio"
                                                                                   name="{{ $field['unique_name'] }}"
                                                                                   id="{{ $field['unique_name'] }}_{{ $optionValue }}"
                                                                                   value="{{ $optionValue }}"
                                                                                {{ old($field['unique_name'], $field['value']) == $optionValue ? 'checked' : '' }}
                                                                                {{ $isRequired ? 'required' : '' }}
                                                                            >
                                                                            <label class="form-check-label"
                                                                                   for="{{ $field['unique_name'] }}_{{ $optionValue }}">
                                                                                {{ $optionLabel }}
                                                                            </label>
                                                                        </div>
                                                                    @endforeach
                                                                @else
                                                                    <p class="text-muted">No options available</p>
                                                                @endif
                                                            </div>

                                                        @elseif($type === 'checkbox')
                                                            <div class="checkbox-group">
                                                                @if(!empty($optionsArray))
                                                                    @php
                                                                        $selectedValues = !empty($field['value']) ? json_decode($field['value'], true) : [];
                                                                        if (!is_array($selectedValues)) {
                                                                            $selectedValues = [$selectedValues];
                                                                        }
                                                                    @endphp
                                                                    @foreach($optionsArray as $optionValue => $optionLabel)
                                                                        <div
                                                                            class="form-check form-check-custom form-check-solid mb-3">
                                                                            <input
                                                                                class="form-check-input checkbox-item"
                                                                                type="checkbox"
                                                                                name="{{ $field['unique_name'] }}[]"
                                                                                id="{{ $field['unique_name'] }}_{{ $optionValue }}"
                                                                                value="{{ $optionValue }}"
                                                                                {{ in_array($optionValue, $selectedValues) ? 'checked' : '' }}
                                                                                data-group="{{ $field['unique_name'] }}"
                                                                                {{ $isRequired ? 'data-required=true' : '' }}
                                                                            >
                                                                            <label class="form-check-label"
                                                                                   for="{{ $field['unique_name'] }}_{{ $optionValue }}">
                                                                                {{ $optionLabel }}
                                                                            </label>
                                                                        </div>
                                                                    @endforeach
                                                                @else
                                                                    <p class="text-muted">No options available</p>
                                                                @endif
                                                            </div>
                                                        @elseif($type === 'select')
                                                            <select name="{{ $field['unique_name'] }}"
                                                                    id="{{ $field['unique_name'] }}"
                                                                    class="form-select form-select-lg form-select-solid"
                                                            {{ $isRequired ? 'required' : '' }}
                                                            @if(!empty($extraArray))
                                                                @foreach($extraArray as $k => $v)
                                                                    @if($k !== 'required')
                                                                        {{ $k }}="{{ $v }}"
                                                                    @endif
                                                                @endforeach
                                                            @endif
                                                            >
                                                            <option value="">Select {{ $field['label'] }}</option>
                                                            @if(!empty($optionsArray))
                                                                @foreach($optionsArray as $optionValue => $optionLabel)
                                                                    <option value="{{ $optionValue }}"
                                                                        {{ old($field['unique_name'], $field['value']) == $optionValue ? 'selected' : '' }}
                                                                    >
                                                                        {{ $optionLabel }}
                                                                    </option>
                                                                    @endforeach
                                                                    @endif
                                                                    </select>

                                                                    @endif

                                                                    @if(!empty($field['hint']))
                                                                        <div
                                                                            class="form-text">{{ $field['hint'] }}</div>
                                                                    @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="card-footer d-flex justify-content-end py-6 px-9">
                                    <button type="submit" class="btn btn-primary" id="kt_settings_submit">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endsection

        @push('scripts')
            <script>
                "use strict";

                var KTSettingsUpdate = function () {
                    var form;
                    var submitButton;
                    var validator;

                    return {
                        init: function () {
                            form = document.querySelector("#kt_settings_form");
                            submitButton = document.querySelector("#kt_settings_submit");

                            if (!form) return;

                            var validationFields = {};

                            var inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
                            inputs.forEach(function (input) {
                                var fieldName = input.getAttribute('name');
                                var fieldType = input.getAttribute('type');

                                if (!fieldName) return;

                                var validationName = fieldName.replace('[]', '');

                                if (fieldType === 'email') {
                                    validationFields[validationName] = {
                                        validators: {
                                            notEmpty: {
                                                message: input.placeholder + ' is required'
                                            },
                                            emailAddress: {
                                                message: 'Please enter a valid email address'
                                            }
                                        }
                                    };
                                } else if (fieldType === 'number') {
                                    validationFields[validationName] = {
                                        validators: {
                                            notEmpty: {
                                                message: input.placeholder + ' is required'
                                            },
                                            numeric: {
                                                message: 'Please enter a valid number'
                                            }
                                        }
                                    };
                                } else if (fieldType === 'url') {
                                    validationFields[validationName] = {
                                        validators: {
                                            notEmpty: {
                                                message: input.placeholder + ' is required'
                                            },
                                            uri: {
                                                message: 'Please enter a valid URL'
                                            }
                                        }
                                    };
                                } else if (fieldType === 'file') {
                                    var acceptAttr = input.getAttribute('accept');
                                    validationFields[validationName] = {
                                        validators: {
                                            notEmpty: {
                                                message: 'Please select a file'
                                            },
                                            file: {
                                                extension: acceptAttr ? acceptAttr.replace(/image\//g, '').replace(/\*/g, 'jpg,jpeg,png,gif') : 'jpg,jpeg,png,gif,pdf,doc,docx',
                                                type: acceptAttr || 'image/jpeg,image/png,image/gif',
                                                maxSize: 5242880, // 5MB
                                                message: 'Please select a valid file (max 5MB)'
                                            }
                                        }
                                    };
                                } else if (input.tagName === 'SELECT') {
                                    validationFields[validationName] = {
                                        validators: {
                                            notEmpty: {
                                                message: 'Please select ' + input.closest('.row').querySelector('label').textContent.trim()
                                            }
                                        }
                                    };
                                } else {
                                    validationFields[validationName] = {
                                        validators: {
                                            notEmpty: {
                                                message: (input.placeholder || 'This field') + ' is required'
                                            }
                                        }
                                    };
                                }
                            });

                            if (Object.keys(validationFields).length > 0) {
                                validator = FormValidation.formValidation(form, {
                                    fields: validationFields,
                                    plugins: {
                                        trigger: new FormValidation.plugins.Trigger(),
                                        bootstrap: new FormValidation.plugins.Bootstrap5({
                                            rowSelector: '.fv-row',
                                            eleInvalidClass: '',
                                            eleValidClass: ''
                                        })
                                    }
                                });
                            }

                            var checkboxGroups = {};
                            document.querySelectorAll('.checkbox-item[data-required="true"]').forEach(function (checkbox) {
                                var group = checkbox.getAttribute('data-group');
                                if (!checkboxGroups[group]) {
                                    checkboxGroups[group] = [];
                                }
                                checkboxGroups[group].push(checkbox);
                            });

                            submitButton.addEventListener("click", function (e) {
                                e.preventDefault();

                                var checkboxValid = true;
                                Object.keys(checkboxGroups).forEach(function (group) {
                                    var checkboxes = checkboxGroups[group];
                                    var checked = checkboxes.filter(function (cb) {
                                        return cb.checked;
                                    });

                                    if (checked.length === 0) {
                                        checkboxValid = false;
                                        // Show error message
                                        var container = checkboxes[0].closest('.checkbox-group');
                                        var errorMsg = container.querySelector('.checkbox-error');
                                        if (!errorMsg) {
                                            errorMsg = document.createElement('div');
                                            errorMsg.className = 'fv-plugins-message-container invalid-feedback checkbox-error';
                                            errorMsg.innerHTML = '<div>Please select at least one option</div>';
                                            container.appendChild(errorMsg);
                                        }
                                        container.classList.add('is-invalid');
                                    } else {
                                        var container = checkboxes[0].closest('.checkbox-group');
                                        var errorMsg = container.querySelector('.checkbox-error');
                                        if (errorMsg) errorMsg.remove();
                                        container.classList.remove('is-invalid');
                                    }
                                });

                                if (!checkboxValid) {
                                    return;
                                }

                                if (validator) {
                                    validator.validate().then(function (status) {
                                        if (status === 'Valid') {
                                            submitButton.setAttribute("data-kt-indicator", "on");
                                            submitButton.disabled = true;
                                            form.submit();
                                        }
                                    });
                                } else {
                                    submitButton.setAttribute("data-kt-indicator", "on");
                                    submitButton.disabled = true;
                                    form.submit();
                                }
                            });
                        }
                    };
                }();

                KTUtil.onDOMContentLoaded(function () {
                    KTSettingsUpdate.init();
                });
            </script>
    @endpush
