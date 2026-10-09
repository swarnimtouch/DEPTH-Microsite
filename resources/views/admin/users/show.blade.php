@extends('layouts.admin')
@section('content')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Post-->
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <!--begin::Container-->
            <div id="kt_content_container" class="container-xxl">
                <!--begin::Layout-->
                <div class="d-flex flex-column flex-lg-row">
                    <!--begin::Sidebar-->
                    <div class="flex-column flex-lg-row-auto w-lg-250px w-xl-350px mb-10">
                        <!--begin::Card-->
                        <div class="card mb-5 mb-xl-8">
                            <!--begin::Card body-->
                            <div class="card-body">
                                <!--begin::Summary-->
                                <!--begin::User Info-->
                                <div class="d-flex flex-center flex-column py-5">
                                    <!--begin::Avatar-->
                                    @php
                                        $avatarField = $activeFields->firstWhere('field_name', 'avatar');
                                    @endphp
                                    @if($avatarField && $user->avatar)
                                        <div class="symbol symbol-100px symbol-circle mb-7">
                                            <img src="{{ asset('storage/' . $user->avatar) }}" alt="User Avatar"/>
                                        </div>
                                    @else
                                        <div class="symbol symbol-100px symbol-circle mb-7">
                                            <div class="symbol-label fs-2 fw-bold text-primary bg-light-primary">
                                                {{ strtoupper(substr($user->first_name ?? 'U', 0, 1)) }}
                                            </div>
                                        </div>
                                    @endif
                                    <!--end::Avatar-->

                                    <!--begin::Name-->
                                    <span class="fs-3 text-gray-800 fw-bolder mb-3">
                                        {{ trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: 'User' }}
                                    </span>
                                    <!--end::Name-->

                                    <!--begin::Email-->
                                    @php
                                        $emailField = $activeFields->firstWhere('field_name', 'email');
                                    @endphp
                                    @if($emailField && $user->email)
                                        <a href="mailto:{{ $user->email }}" class="fs-5 fw-bold text-gray-600 text-hover-primary mb-6">
                                            {{ $user->email }}
                                        </a>
                                    @endif
                                    <!--end::Email-->
                                </div>
                                <!--end::User Info-->

                                <!--begin::Details-->
                                <div class="d-flex flex-stack fs-5 py-3">
                                    <div class="fw-bolder">User ID:</div>
                                    <div class="text-gray-600">#{{ $user->id }}</div>
                                </div>
                                <div class="separator separator-dashed my-3"></div>

                                @php
                                    $mobileField = $activeFields->firstWhere('field_name', 'mobile_number');
                                @endphp
                                @if($mobileField && $user->mobile)
                                    <div class="d-flex flex-stack fs-5 py-3">
                                        <div class="fw-bolder">Mobile:</div>
                                        <div class="text-gray-600">
                                            <a href="tel:{{ $user->mobile }}" class="text-gray-600 text-hover-primary">
                                                {{ $user->mobile }}
                                            </a>
                                        </div>
                                    </div>
                                    <div class="separator separator-dashed my-3"></div>
                                @endif

                                @php
                                    $altMobileField = $activeFields->firstWhere('field_name', 'alternative_mobile_number');
                                @endphp
                                @if($altMobileField && $user->alternative_mobile)
                                    <div class="d-flex flex-stack fs-5 py-3">
                                        <div class="fw-bolder">Alternative Mobile:</div>
                                        <div class="text-gray-600">
                                            <a href="tel:{{ $user->alternative_mobile }}" class="text-gray-600 text-hover-primary">
                                                {{ $user->alternative_mobile }}
                                            </a>
                                        </div>
                                    </div>
                                    <div class="separator separator-dashed my-3"></div>
                                @endif

                                <div class="d-flex flex-stack fs-5 py-3">
                                    <div class="fw-bolder">Created At:</div>
                                    <div class="text-gray-600">    {{ $user->created_at ? $user->created_at->format('d M, Y') : '—' }}
                                    </div>
                                </div>
                                <!--end::Details-->
                            </div>
                            <!--end::Card body-->
                        </div>
                        <!--end::Card-->

                        <!--begin::Actions-->
                        <div class="card">
                            <div class="card-body">
                                <a href="{{ route('admin.user.index') }}" class="btn btn-light w-100 mb-3">
                                    <span class="svg-icon svg-icon-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <path d="M9.60001 11H21C21.6 11 22 11.4 22 12C22 12.6 21.6 13 21 13H9.60001V11Z" fill="black"/>
                                            <path opacity="0.3" d="M9.6 20V4L2.3 11.3C1.9 11.7 1.9 12.3 2.3 12.7L9.6 20Z" fill="black"/>
                                        </svg>
                                    </span>
                                    Back
                                </a>

                            </div>
                        </div>
                        <!--end::Actions-->
                    </div>
                    <!--end::Sidebar-->

                    <!--begin::Content-->
                    <div class="flex-lg-row-fluid ms-lg-15">
                        <!--begin::Card-->
                        <div class="card">
                            <!--begin::Card header-->
                            <div class="card-header">
                                <div class="card-title">
                                    <h3 class="fw-bolder m-0">User Details</h3>
                                </div>
                            </div>
                            <!--end::Card header-->

                            <!--begin::Card body-->
                            <div class="card-body p-9">
                                <div class="row mb-7">
                                    @php
                                        $nameFieldDisplayed = false;
                                        $skipFields = ['avatar', 'password', 'email', 'mobile_number', 'alternative_mobile_number'];
                                        $displayedCount = 0;
                                    @endphp

                                    @foreach($activeFields as $field)
                                        @php
                                            $fieldName = $field->field_name;

                                            // Skip fields already shown in sidebar
                                            if (in_array($fieldName, $skipFields)) {
                                                continue;
                                            }

                                            // Skip last_name if first_name was already shown
                                            if ($fieldName == 'last_name' && $nameFieldDisplayed) {
                                                continue;
                                            }

                                            // Field name mapping
                                            $fieldMapping = [
                                                'mobile_number' => 'mobile',
                                                'alternative_mobile_number' => 'alternative_mobile',
                                            ];

                                            $dbFieldName = $fieldMapping[$fieldName] ?? $fieldName;
                                            $value = $user->$dbFieldName;
                                        @endphp

                                        @if(in_array($fieldName, ['first_name', 'last_name']))
                                            @if(!$nameFieldDisplayed)
                                                <div class="col-lg-6">
                                                    <div class="fw-bold text-gray-600 mb-2">Full Name:</div>
                                                    <div class="fw-bolder fs-6 text-gray-800">
                                                        {{ trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: '—' }}
                                                    </div>
                                                </div>
                                                @php
                                                    $nameFieldDisplayed = true;
                                                    $displayedCount++;
                                                @endphp
                                            @endif
                                        @elseif($fieldName == 'name')
                                            <div class="col-lg-6">
                                                <div class="fw-bold text-gray-600 mb-2">{{ $field->label }}:</div>
                                                <div class="fw-bolder fs-6 text-gray-800">
                                                    {{ trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: '—' }}
                                                </div>
                                            </div>
                                            @php $displayedCount++; @endphp
                                        @else
                                            <div class="col-lg-6">
                                                <div class="fw-bold text-gray-600 mb-2">{{ $field->label }}:</div>
                                                <div class="fw-bolder fs-6 text-gray-800">
                                                    @if(!empty($value))
                                                        {{ $value }}
                                                    @else
                                                        —
                                                    @endif
                                                </div>
                                            </div>
                                            @php $displayedCount++; @endphp
                                        @endif

                                        @if($displayedCount % 2 == 0 && !$loop->last)
                                </div><div class="row mb-7">
                                    @endif
                                    @endforeach
                                </div>
                            </div>
                            <!--end::Card body-->
                        </div>
                        <!--end::Card-->
                    </div>
                    <!--end::Content-->
                </div>
                <!--end::Layout-->
            </div>
            <!--end::Container-->
        </div>
        <!--end::Post-->
    </div>
@endsection

@push('scripts')
@endpush
