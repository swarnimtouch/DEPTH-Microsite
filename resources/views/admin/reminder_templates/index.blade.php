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

                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title gap-3">
                            <input type="text" data-kt-reminder-filter="search" class="form-control form-control-solid w-250px" placeholder="Search templates"/>
                            <select data-kt-reminder-filter="channel" class="form-select form-select-solid w-200px">
                                <option value="">All Channels</option>
                                @foreach($channels as $channel)
                                    <option value="{{ $channel }}">{{ ucfirst($channel) }}</option>
                                @endforeach
                            </select>
                            <select data-kt-reminder-filter="timing" class="form-select form-select-solid w-250px">
                                <option value="">All Timings</option>
                                @foreach($timings as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="card-toolbar">
                            <a href="{{ route('admin.reminder_templates.logs') }}" class="btn btn-light-primary me-2">Reminder Logs</a>
                            <a href="{{ route('admin.reminder_templates.add_edit_form') }}" class="btn btn-primary">Add Template</a>
                        </div>
                    </div>

                    <div class="card-body pt-0">
                        <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_reminders">
                            <thead>
                            <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                                <th>Name</th>
                                <th>Channel</th>
                                <th>Timing</th>
                                <th>Subject</th>
                                <th>Status</th>
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
            const timings = @json($timings);
            const table = $('#kt_table_reminders').DataTable({
                processing: true,
                serverSide: true,
                searchDelay: 500,
                ajax: {
                    url: '{{ route("admin.reminder_templates.datatable") }}',
                    data: d => {
                        d.search = $('[data-kt-reminder-filter="search"]').val();
                        d.channel = $('[data-kt-reminder-filter="channel"]').val();
                        d.timing = $('[data-kt-reminder-filter="timing"]').val();
                    }
                },
                columns: [
                    {data: 'name'},
                    {data: 'channel', render: data => `<span class="badge badge-light-primary">${data}</span>`},
                    {data: 'timing', render: data => timings[data] || data},
                    {data: 'subject', defaultContent: '-'},
                    {
                        data: 'status',
                        render: (data, type, row) => `<label class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input status-change" data-id="${row.id}" type="checkbox" ${row.status === 'active' ? 'checked="checked"' : ''}/>
                        </label>`
                    },
                    {
                        data: 'id',
                        orderable: false,
                        render: id => `<a href="{{ route('admin.reminder_templates.add_edit_form') }}/${id}" class="btn btn-sm" title="Edit"><i class="bi bi-pencil-fill"></i></a>`
                    }
                ]
            });

            $('[data-kt-reminder-filter="search"], [data-kt-reminder-filter="channel"], [data-kt-reminder-filter="timing"]').on('keyup change', function () {
                table.draw();
            });

            document.addEventListener('change', e => {
                if (!e.target.classList.contains('status-change')) return;
                const checkbox = e.target;
                $.post('{{ route("admin.reminder_templates.status_change", ":id") }}'.replace(':id', checkbox.dataset.id), {_token: '{{ csrf_token() }}'})
                    .done(() => toastr.success('Status updated successfully'))
                    .fail(() => {
                        checkbox.checked = !checkbox.checked;
                        toastr.error('Status update failed');
                    });
            });
        });
    </script>
@endpush
