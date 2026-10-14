@extends('layouts.admin')

@section('content')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">
                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title gap-3 flex-wrap">
                            <input type="text" data-kt-certificate-filter="search" class="form-control form-control-solid w-250px" placeholder="Search doctor or chapter"/>
                            <select data-kt-certificate-filter="module" class="form-select form-select-solid w-250px">
                                <option value="">All Chapters</option>
                                @foreach($modules as $module)
                                    <option value="{{ $module->id }}">{{ $module->title }}</option>
                                @endforeach
                            </select>
                            <select data-kt-certificate-filter="status" class="form-select form-select-solid w-200px">
                                <option value="">All Statuses</option>
                                <option value="available">Available</option>
                                <option value="downloaded">Downloaded</option>
                            </select>
                        </div>
                        <div class="card-toolbar">
                            <a href="{{ route('admin.certificate_logs.export') }}" class="btn btn-light-primary" id="exportCertificateLogsBtn">
                                <i class="bi bi-file-earmark-excel me-2"></i>Export Excel
                            </a>
                        </div>
                    </div>

                    <div class="card-body pt-0">
                        <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_certificate_logs">
                            <thead>
                            <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                                <th>Doctor</th>
                                <th>Chapter</th>
                                <th>Type</th>
                                <th>Issued At</th>
                                <th>Downloads</th>
                                <th>First Download</th>
                                <th>Latest Download</th>
                                <th>Status</th>
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
    <script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
    <script>
        "use strict";
        KTUtil.onDOMContentLoaded(function () {
            const escapeHtml = value => $('<div>').text(value || '').html();
            const table = $('#kt_table_certificate_logs').DataTable({
                processing: true,
                serverSide: true,
                searchDelay: 500,
                order: [],
                ajax: {
                    url: '{{ route('admin.certificate_logs.datatable') }}',
                    data: d => {
                        d.search = $('[data-kt-certificate-filter="search"]').val();
                        d.module_id = $('[data-kt-certificate-filter="module"]').val();
                        d.status = $('[data-kt-certificate-filter="status"]').val();
                    }
                },
                columns: [
                    {
                        data: null,
                        render: row => `<div class="d-flex flex-column">
                            <span class="fw-semibold text-gray-800">${escapeHtml(row.user_name)}</span>
                            <span class="text-muted fs-7">${escapeHtml(row.email)}</span>
                            <span class="text-muted fs-8">${escapeHtml(row.mobile)}</span>
                        </div>`
                    },
                    {data: 'module_title'},
                    {data: 'certificate_type'},
                    {data: 'issued_at'},
                    {
                        data: 'download_count',
                        render: count => `<span class="badge badge-light-primary">${count}</span>`
                    },
                    {data: 'first_downloaded_at'},
                    {data: 'latest_downloaded_at'},
                    {
                        data: 'status',
                        render: status => `<span class="badge ${status === 'Downloaded' ? 'badge-light-success' : 'badge-light-warning'}">${status}</span>`
                    }
                ]
            });

            const syncExportUrl = () => {
                const url = new URL('{{ route('admin.certificate_logs.export') }}', window.location.origin);
                const search = $('[data-kt-certificate-filter="search"]').val();
                const moduleId = $('[data-kt-certificate-filter="module"]').val();
                const status = $('[data-kt-certificate-filter="status"]').val();

                if (search) url.searchParams.set('search', search);
                if (moduleId) url.searchParams.set('module_id', moduleId);
                if (status) url.searchParams.set('status', status);
                document.getElementById('exportCertificateLogsBtn')?.setAttribute('href', url.toString());
            };

            $('[data-kt-certificate-filter="search"], [data-kt-certificate-filter="module"], [data-kt-certificate-filter="status"]').on('keyup change', function () {
                syncExportUrl();
                table.draw();
            });

            syncExportUrl();
        });
    </script>
@endpush
