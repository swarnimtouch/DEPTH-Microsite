@extends('layouts.admin')

@php
    $isSubAdmin = auth('admin')->user()?->role === 'sub_admin';
@endphp

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

                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title gap-3">
                            <input type="text" data-kt-feedback-table-filter="search" class="form-control form-control-solid w-250px" placeholder="Search user or chapter"/>
                            <select data-kt-feedback-table-filter="module" class="form-select form-select-solid w-250px">
                                <option value="">All Chapters</option>
                                @foreach($modules as $module)
                                    <option value="{{ $module->id }}">{{ $module->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="card-toolbar gap-2">
                            <a href="{{ route('admin.quizzes.submissions.export') }}" class="btn btn-light-primary" id="exportFeedbackBtn">
                                <i class="bi bi-file-earmark-excel me-2"></i>Export Excel
                            </a>
                            @unless($isSubAdmin)
                            <a href="{{ route('admin.quizzes') }}" class="btn btn-light">Feedback Questions</a>
                            @endunless
                        </div>
                    </div>

                    <div class="card-body pt-0">
                        <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_feedback_submissions">
                            <thead>
                            <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                                <th>Doctor</th>
                                <th>Email</th>
                                <th>Mobile</th>
                                <th>Chapter</th>
                                <th>Submitted At</th>
                                <th>Replies</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{asset('assets/plugins/custom/datatables/datatables.bundle.js')}}"></script>
    <script>
        "use strict";
        KTUtil.onDOMContentLoaded(function () {
            const table = $('#kt_table_feedback_submissions').DataTable({
                processing: true,
                serverSide: true,
                searchDelay: 500,
                ajax: {
                    url: '{{ route("admin.quizzes.submissions.datatable") }}',
                    data: d => {
                        d.search = $('[data-kt-feedback-table-filter="search"]').val();
                        d.module_id = $('[data-kt-feedback-table-filter="module"]').val();
                    }
                },
                columns: [
                    {data: 'user.name', defaultContent: 'Delegate'},
                    {data: 'user.email', defaultContent: '-'},
                    {data: 'user.mobile', defaultContent: '-'},
                    {data: 'module.title', defaultContent: '-'},
                    {data: 'quiz_completed_at'},
                    {
                        data: 'text_replies_count',
                        render: count => count > 0 ? `<span class="badge badge-light-success">${count} replied</span>` : '<span class="badge badge-light">No replies</span>'
                    },
                    {
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        render: id => `<a href="{{ route('admin.quizzes.submissions') }}/${id}" class="btn btn-sm btn-light-primary">{{ $isSubAdmin ? 'View' : 'View / Reply' }}</a>`
                    }
                ]
            });

            const syncExportUrl = () => {
                const url = new URL('{{ route('admin.quizzes.submissions.export') }}', window.location.origin);
                const search = $('[data-kt-feedback-table-filter="search"]').val();
                const moduleId = $('[data-kt-feedback-table-filter="module"]').val();
                if (search) url.searchParams.set('search', search);
                if (moduleId) url.searchParams.set('module_id', moduleId);
                document.getElementById('exportFeedbackBtn')?.setAttribute('href', url.toString());
            };

            $('[data-kt-feedback-table-filter="search"], [data-kt-feedback-table-filter="module"]').on('keyup change', function () {
                syncExportUrl();
                table.draw();
            });
            syncExportUrl();
        });
    </script>
@endpush
