@extends('layouts.admin')

@section('content')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">
                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title gap-3">
                            <select data-kt-reminder-log-filter="channel" class="form-select form-select-solid w-200px">
                                <option value="">All Channels</option>
                                @foreach($channels as $channel)
                                    <option value="{{ $channel }}">{{ ucfirst($channel) }}</option>
                                @endforeach
                            </select>
                            <select data-kt-reminder-log-filter="status" class="form-select form-select-solid w-200px">
                                <option value="">All Statuses</option>
                                <option value="sent">Sent</option>
                                <option value="skipped">Skipped</option>
                                <option value="failed">Failed</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                        <div class="card-toolbar">
                            <a href="{{ route('admin.reminder_templates') }}" class="btn btn-light">Templates</a>
                        </div>
                    </div>

                    <div class="card-body pt-0">
                        <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_reminder_logs">
                            <thead>
                            <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                                <th>Chapter</th>
                                <th>User</th>
                                <th>Channel</th>
                                <th>Timing</th>
                                <th>Recipient</th>
                                <th>Status</th>
                                <th>Sent At</th>
                                <th>Response</th>
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
            const table = $('#kt_table_reminder_logs').DataTable({
                processing: true,
                serverSide: true,
                searchDelay: 500,
                ajax: {
                    url: '{{ route("admin.reminder_templates.logs.datatable") }}',
                    data: d => {
                        d.channel = $('[data-kt-reminder-log-filter="channel"]').val();
                        d.status = $('[data-kt-reminder-log-filter="status"]').val();
                    }
                },
                columns: [
                    {data: 'module.title', defaultContent: '-'},
                    {
                        data: 'user.name',
                        render: (data, type, row) => `<div>${data || '-'}</div><div class="text-muted fs-8">${row.user?.email || ''}</div>`
                    },
                    {data: 'channel', render: data => `<span class="badge badge-light-primary">${data}</span>`},
                    {data: 'timing'},
                    {data: 'recipient'},
                    {
                        data: 'status',
                        render: data => {
                            const klass = data === 'sent' ? 'success' : (data === 'failed' ? 'danger' : 'warning');
                            return `<span class="badge badge-light-${klass}">${data}</span>`;
                        }
                    },
                    {data: 'sent_at'},
                    {
                        data: 'response',
                        render: data => `<div class="text-truncate" style="max-width: 280px;" title="${$('<div>').text(data || '').html()}">${$('<div>').text(data || '').html()}</div>`
                    }
                ]
            });

            $('[data-kt-reminder-log-filter="channel"], [data-kt-reminder-log-filter="status"]').on('change', function () {
                table.draw();
            });
        });
    </script>
@endpush
