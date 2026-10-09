@extends('layouts.admin')

@section('content')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">
                <div class="card">
                    <div class="card-header border-0 pt-6">
                        <div class="card-title gap-3">
                            <input type="text" data-kt-time-report-filter="search" class="form-control form-control-solid w-250px" placeholder="Search user or chapter"/>
                            <select data-kt-time-report-filter="module" class="form-select form-select-solid w-250px">
                                <option value="">All Chapters</option>
                                @foreach($modules as $module)
                                    <option value="{{ $module->id }}">{{ $module->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="card-toolbar d-flex flex-wrap gap-2">
                            <a href="{{ route('admin.reports.module_view_time.download_questions') }}" class="btn btn-light-primary" id="downloadQuestionsBtn">
                                <i class="bi bi-question-circle me-2"></i>Session Questions
                            </a>
                            <a href="{{ route('admin.reports.module_view_time.download_attendees') }}" class="btn btn-light-primary" id="downloadAttendeesBtn">
                                <i class="bi bi-people me-2"></i>Session Attendees
                            </a>
                            <a href="{{ route('admin.reports.module_view_time.download_users') }}" class="btn btn-light-primary" id="downloadUsersBtn">
                                <i class="bi bi-person-lines-fill me-2"></i>All Users
                            </a>
                            <a href="{{ route('admin.reports.module_view_time.download') }}" class="btn btn-primary" id="downloadReportBtn">
                                <i class="bi bi-download me-2"></i>Time Report
                            </a>
                        </div>
                    </div>

                    <div class="card-body pt-0">
                        <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_time_report">
                            <thead>
                            <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                                <th>Doctor</th>
                                <th>Email</th>
                                <th>Mobile</th>
                                <th>Chapter</th>
                                <th>Event Time</th>
                                <th>During Event</th>
                                <th>Post Event</th>
                                <th>Total</th>
                                <th>First Seen</th>
                                <th>Last Seen</th>
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
            const table = $('#kt_table_time_report').DataTable({
                processing: true,
                serverSide: true,
                searchDelay: 500,
                ajax: {
                    url: '{{ route("admin.reports.module_view_time.datatable") }}',
                    data: d => {
                        d.search = $('[data-kt-time-report-filter="search"]').val();
                        d.module_id = $('[data-kt-time-report-filter="module"]').val();
                    }
                },
                columns: [
                    {data: 'user_name'},
                    {data: 'email'},
                    {data: 'mobile'},
                    {data: 'module_title'},
                    {
                        data: null,
                        render: row => `<div>${row.event_start}</div><div class="text-muted fs-8">${row.event_end}</div>`
                    },
                    {data: 'during_event_time'},
                    {data: 'post_event_time'},
                    {data: 'total_time'},
                    {data: 'first_seen'},
                    {data: 'last_seen'},
                ]
            });

            const syncDownloadUrl = () => {
                const timeUrl = new URL('{{ route('admin.reports.module_view_time.download') }}', window.location.origin);
                const attendeesUrl = new URL('{{ route('admin.reports.module_view_time.download_attendees') }}', window.location.origin);
                const questionsUrl = new URL('{{ route('admin.reports.module_view_time.download_questions') }}', window.location.origin);
                const usersUrl = new URL('{{ route('admin.reports.module_view_time.download_users') }}', window.location.origin);
                const search = $('[data-kt-time-report-filter="search"]').val();
                const moduleId = $('[data-kt-time-report-filter="module"]').val();

                if (search) {
                    timeUrl.searchParams.set('search', search);
                    attendeesUrl.searchParams.set('search', search);
                    usersUrl.searchParams.set('search', search);
                }

                if (moduleId) {
                    timeUrl.searchParams.set('module_id', moduleId);
                    attendeesUrl.searchParams.set('module_id', moduleId);
                    questionsUrl.searchParams.set('module_id', moduleId);
                }

                document.getElementById('downloadReportBtn')?.setAttribute('href', timeUrl.toString());
                document.getElementById('downloadAttendeesBtn')?.setAttribute('href', attendeesUrl.toString());
                document.getElementById('downloadQuestionsBtn')?.setAttribute('href', questionsUrl.toString());
                document.getElementById('downloadUsersBtn')?.setAttribute('href', usersUrl.toString());
            };

            $('[data-kt-time-report-filter="search"], [data-kt-time-report-filter="module"]').on('keyup change', function () {
                syncDownloadUrl();
                table.draw();
            });

            syncDownloadUrl();
        });
    </script>
@endpush
