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
                        <div class="card-title gap-3 flex-wrap">
                            <input type="text" data-kt-comments-table-filter="search" class="form-control form-control-solid w-250px" placeholder="Search comments"/>
                            <select data-kt-comments-table-filter="module" class="form-select form-select-solid w-250px">
                                <option value="">All Chapters</option>
                                @foreach($modules as $module)
                                    <option value="{{ $module->id }}">{{ $module->title }}</option>
                                @endforeach
                            </select>
                            <select data-kt-comments-table-filter="status" class="form-select form-select-solid w-200px">
                                <option value="">All Statuses</option>
                                <option value="publish">Published</option>
                                <option value="unpublish">Unpublished</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                        <div class="card-toolbar">
                            <a href="{{ route('admin.comments.export') }}" class="btn btn-light-primary" id="exportCommentsBtn">
                                <i class="bi bi-file-earmark-excel me-2"></i>Export Excel
                            </a>
                        </div>
                    </div>

                    <div class="card-body pt-0">
                        <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_comments">
                            <thead>
                            <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                                <th>Chapter</th>
                                <th>User</th>
                                <th>Comment</th>
                                <th>Posted</th>
                                <th>Status</th>
                                @unless($isSubAdmin)
                                    <th>Publish</th>
                                    <th>Actions</th>
                                @endunless
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
            const escapeHtml = value => $('<div>').text(value || '').html();
            const table = $('#kt_table_comments').DataTable({
                processing: true,
                serverSide: true,
                searchDelay: 500,
                order: [],
                ajax: {
                    url: '{{ route("admin.comments.datatable") }}',
                    data: d => {
                        d.search = $('[data-kt-comments-table-filter="search"]').val();
                        d.module_id = $('[data-kt-comments-table-filter="module"]').val();
                        d.status = $('[data-kt-comments-table-filter="status"]').val();
                    }
                },
                columns: [
                    {
                        data: 'module.title',
                        defaultContent: '-',
                        render: data => `<span class="fw-semibold text-gray-800">${escapeHtml(data || '-')}</span>`
                    },
                    {
                        data: 'user',
                        render: user => {
                            const name = escapeHtml(user?.name || 'Delegate');
                            const email = escapeHtml(user?.email || '');
                            return `<div class="d-flex flex-column">
                                <span class="fw-semibold text-gray-800">${name}</span>
                                <span class="text-muted fs-7">${email}</span>
                            </div>`;
                        }
                    },
                    {
                        data: 'comment',
                        render: data => `<div class="text-gray-700" style="max-width: 520px; white-space: normal;">${escapeHtml(data)}</div>`
                    },
                    {
                        data: 'created_at',
                        render: data => data ? new Date(data).toLocaleString() : '-'
                    },
                    {
                        data: 'status',
                        render: status => {
                            const badge = status === 'publish' ? 'badge-light-success' : (status === 'pending' ? 'badge-light-warning' : 'badge-light-danger');
                            const label = status === 'publish' ? 'Published' : (status === 'pending' ? 'Pending' : 'Unpublished');
                            return `<span class="badge ${badge}">${label}</span>`;
                        }
                    }
                    @unless($isSubAdmin)
                    ,
                    {
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        render: (id, type, row) => `<label class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input comment-status-change" data-id="${id}" type="checkbox" ${row.status === 'publish' ? 'checked="checked"' : ''}/>
                        </label>`
                    },
                    {
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        render: id => `<button class="btn btn-sm delete-comment" data-id="${id}" title="Delete"><i class="bi bi-trash"></i></button>`
                    }
                    @endunless
                ]
            });

            $('[data-kt-comments-table-filter="search"], [data-kt-comments-table-filter="module"], [data-kt-comments-table-filter="status"]').on('keyup change', function () {
                syncExportUrl();
                table.draw();
            });

            const syncExportUrl = () => {
                const url = new URL('{{ route('admin.comments.export') }}', window.location.origin);
                const search = $('[data-kt-comments-table-filter="search"]').val();
                const moduleId = $('[data-kt-comments-table-filter="module"]').val();
                const status = $('[data-kt-comments-table-filter="status"]').val();
                if (search) url.searchParams.set('search', search);
                if (moduleId) url.searchParams.set('module_id', moduleId);
                if (status) url.searchParams.set('status', status);
                document.getElementById('exportCommentsBtn')?.setAttribute('href', url.toString());
            };
            syncExportUrl();

            @unless($isSubAdmin)
            document.addEventListener('change', e => {
                if (!e.target.classList.contains('comment-status-change')) return;

                const checkbox = e.target;
                $.post('{{ route("admin.comments.status_change", ":id") }}'.replace(':id', checkbox.dataset.id), {_token: '{{ csrf_token() }}'})
                    .done(response => {
                        toastr.success(response.status === 'publish' ? 'Comment published' : 'Comment unpublished');
                        table.draw(false);
                    })
                    .fail(() => {
                        checkbox.checked = !checkbox.checked;
                        toastr.error('Status update failed');
                    });
            });
            @endunless

            document.addEventListener('click', e => {
                const button = e.target.closest('.delete-comment');
                if (!button) return;

                Swal.fire({
                    text: "Are you sure you want to delete this comment?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, delete",
                    cancelButtonText: "Cancel"
                }).then(result => {
                    if (!result.isConfirmed) return;

                    $.post('{{ route("admin.comments.delete", ":id") }}'.replace(':id', button.dataset.id), {_token: '{{ csrf_token() }}'})
                        .done(() => {
                            toastr.success('Comment deleted successfully');
                            table.draw(false);
                        })
                        .fail(() => toastr.error('Delete failed'));
                });
            });
        });
    </script>
@endpush
