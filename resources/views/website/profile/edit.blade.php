@extends('layouts.dashboard')

@section('dashboard_header_center')
    <div>
        <h6 class="fw-bold text-dark-green mb-0">My Profile</h6>
    </div>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-xxl-9 col-xl-10">
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

            <div class="profile-edit-shell bg-white">
                <div class="profile-edit-header d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <img src="{{ $doctor->profile_image }}" alt="{{ $doctor->name }}" class="profile-edit-avatar">
                        <div>
                            <span class="badge-custom d-inline-block px-3 py-2 mb-2 rounded-pill fw-bold">DOCTOR PROFILE</span>
                            <h3 class="fw-bolder text-dark-green mb-1">Dr. {{ $doctor->name }}</h3>
                            <p class="text-muted mb-0">{{ $doctor->hospital ?: 'Hospital details not added' }}</p>
                        </div>
                    </div>
                    <a href="{{ route('course.dashboard') }}" class="btn btn-outline-purple px-4 py-2 fw-semibold">Back to Home</a>
                </div>

                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="profile-edit-form">
                    @csrf

                    <div class="row g-4">
                        <div class="col-lg-6">
                            <label class="form-label custom-form-label">Name</label>
                            <input type="text" name="name" class="form-control custom-form-control" value="{{ old('name', $doctor->name) }}" required>
                        </div>

                        <div class="col-lg-6">
                            <label class="form-label custom-form-label">Hospital</label>
                            <input type="text" name="hospital" class="form-control custom-form-control" value="{{ old('hospital', $doctor->hospital) }}" required>
                        </div>

                        <div class="col-lg-6">
                            <label class="form-label custom-form-label">Country</label>
                            <select name="country" class="form-select custom-form-control" required>
                                <option value="" disabled {{ old('country', $doctor->country) ? '' : 'selected' }}>Choose Country</option>
                                @foreach($country as $item)
                                    <option value="{{ $item->name }}" {{ old('country', $doctor->country) == $item->name ? 'selected' : '' }}>{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-6">
                            <label class="form-label custom-form-label">Mobile Number</label>
                            <input type="tel" name="mobile" class="form-control custom-form-control" value="{{ old('mobile', $doctor->mobile) }}" required>
                        </div>

                        <div class="col-lg-6">
                            <label class="form-label custom-form-label">Email ID</label>
                            <input type="email" name="email" class="form-control custom-form-control" value="{{ old('email', $doctor->email) }}" required>
                        </div>

                        <div class="col-lg-6">
                            <label class="form-label custom-form-label">Profile Image</label>
                            <input type="file" name="profile_image" class="form-control custom-form-control" accept=".jpg,.jpeg,.png,.webp">
                            <div class="form-text">Allowed: JPG, PNG, WEBP. Maximum size 2 MB.</div>
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row justify-content-end gap-3 mt-5">
                        <a href="{{ route('course.dashboard') }}" class="btn btn-light px-4 py-2 fw-semibold">Cancel</a>
                        <button type="submit" class="btn btn-primary-custom px-5 py-2 fw-semibold">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
