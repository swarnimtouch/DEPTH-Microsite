@extends('layouts.admin')

@section('content')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title">
                            <div>
                                <h3 class="fw-bold mb-1">Sub Admin Accounts</h3>
                                <div class="text-muted fs-7">These credentials use the same {{ route('admin.login') }} login page.</div>
                            </div>
                        </div>
                        <div class="card-toolbar">
                            <a href="{{ route('admin.sub_admins.form') }}" class="btn btn-primary">
                                <i class="bi bi-plus-lg me-2"></i>Add Sub Admin
                            </a>
                        </div>
                    </div>
                    <div class="card-body pt-4">
                        <div class="alert alert-light-primary border border-primary mb-6">
                            Sub admins can view Dashboard, Doctors, Submitted Feedback, Comments and Time Reports, and export those records to Excel.
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle table-row-dashed gy-5">
                                <thead>
                                <tr class="text-muted fw-bolder fs-7 text-uppercase">
                                    <th>Name</th>
                                    <th>Email / Login ID</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($subAdmins as $subAdmin)
                                    <tr>
                                        <td class="fw-semibold">{{ $subAdmin->name }}</td>
                                        <td>{{ $subAdmin->email }}</td>
                                        <td><span class="badge {{ $subAdmin->status === 'active' ? 'badge-light-success' : 'badge-light-danger' }}">{{ ucfirst($subAdmin->status) }}</span></td>
                                        <td>{{ $subAdmin->created_at?->format('d M Y, h:i A') }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.sub_admins.form', $subAdmin->id) }}" class="btn btn-sm btn-light-primary me-2">Edit</a>
                                            <form method="POST" action="{{ route('admin.sub_admins.delete', $subAdmin->id) }}" class="d-inline" onsubmit="return confirm('Delete this sub admin account?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-light-danger" type="submit">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-10">No sub admin account has been created yet.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
