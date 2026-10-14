@extends('layouts.admin')

@section('content')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">
                <form method="POST" action="{{ route('admin.sub_admins.save', $subAdmin->exists ? $subAdmin->id : null) }}">
                    @csrf
                    @if($subAdmin->exists) @method('PUT') @endif
                    <div class="card">
                        <div class="card-header border-0 pt-6">
                            <div class="card-title"><h3 class="fw-bold mb-0">{{ $subAdmin->exists ? 'Edit Sub Admin' : 'Add Sub Admin' }}</h3></div>
                        </div>
                        <div class="card-body">
                            @if($errors->any())
                                <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                            @endif
                            <div class="row g-6">
                                <div class="col-md-6">
                                    <label class="form-label required">Name</label>
                                    <input name="name" class="form-control form-control-solid" value="{{ old('name', $subAdmin->name) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required">Email / Login ID</label>
                                    <input type="email" name="email" class="form-control form-control-solid" value="{{ old('email', $subAdmin->email) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label {{ $subAdmin->exists ? '' : 'required' }}">Password</label>
                                    <input type="password" name="password" class="form-control form-control-solid" {{ $subAdmin->exists ? '' : 'required' }} autocomplete="new-password">
                                    @if($subAdmin->exists)<div class="text-muted fs-8 mt-2">Leave blank to keep the current password.</div>@endif
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label {{ $subAdmin->exists ? '' : 'required' }}">Confirm Password</label>
                                    <input type="password" name="password_confirmation" class="form-control form-control-solid" {{ $subAdmin->exists ? '' : 'required' }} autocomplete="new-password">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required">Status</label>
                                    <select name="status" class="form-select form-select-solid" required>
                                        <option value="active" @selected(old('status', $subAdmin->status) === 'active')>Active</option>
                                        <option value="inactive" @selected(old('status', $subAdmin->status) === 'inactive')>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-end gap-3">
                            <a href="{{ route('admin.sub_admins') }}" class="btn btn-light">Cancel</a>
                            <button class="btn btn-primary" type="submit">Save Sub Admin</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
