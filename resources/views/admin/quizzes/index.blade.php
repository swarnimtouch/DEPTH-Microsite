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
                            <input type="text" data-kt-quiz-table-filter="search" class="form-control form-control-solid w-250px" placeholder="Search questions"/>
                            <select data-kt-quiz-table-filter="module" class="form-select form-select-solid w-250px">
                                <option value="">All Chapters</option>
                                @foreach($modules as $module)
                                    <option value="{{ $module->id }}">{{ $module->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="card-toolbar">
                            <div class="d-flex justify-content-end" data-kt-quiz-table-toolbar="base">
                                <a href="{{ route('admin.quizzes.submissions') }}" class="btn btn-light-primary me-2">Submitted Feedback</a>
                                <a href="{{ route('admin.quizzes.add_edit_form') }}" class="btn btn-primary">Add Question</a>
                            </div>

                            <div class="d-flex justify-content-end align-items-center d-none" data-kt-quiz-table-toolbar="selected">
                                <div class="fw-bolder me-5">
                                    <span class="me-2" data-kt-quiz-table-select="selected_count"></span>Selected
                                </div>
                                <button type="button" class="btn btn-danger" data-kt-quiz-table-select="delete_selected">
                                    Delete Selected
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card-body pt-0">
                        <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_quizzes">
                            <thead>
                            <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                                <th class="w-10px pe-2 no-sort">
                                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                        <input class="form-check-input multiple-delete-checkbox" type="checkbox" data-kt-check="true" data-kt-check-target="#kt_table_quizzes .row-checkbox" value="1"/>
                                    </div>
                                </th>
                                <th>Chapter</th>
                                <th>Question</th>
                                <th>Type</th>
                                <th>Order</th>
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
            const toolbarBase = document.querySelector('[data-kt-quiz-table-toolbar="base"]');
            const toolbarSelected = document.querySelector('[data-kt-quiz-table-toolbar="selected"]');
            const selectedCountEl = document.querySelector('[data-kt-quiz-table-select="selected_count"]');
            const table = $('#kt_table_quizzes').DataTable({
                processing: true,
                serverSide: true,
                searchDelay: 500,
                ajax: {
                    url: '{{ route("admin.quizzes.datatable") }}',
                    data: d => {
                        d.search = $('[data-kt-quiz-table-filter="search"]').val();
                        d.module_id = $('[data-kt-quiz-table-filter="module"]').val();
                    }
                },
                columns: [
                    {
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        render: id => `<div class="form-check form-check-sm form-check-custom form-check-solid"><input class="form-check-input row-checkbox" type="checkbox" value="${id}" /></div>`
                    },
                    {data: 'module.title', defaultContent: '-'},
                    {
                        data: 'question',
                        render: data => `<div class="text-truncate" style="max-width: 520px;" title="${$('<div>').text(data || '').html()}">${$('<div>').text(data || '').html()}</div>`
                    },
                    {
                        data: 'question_type',
                        render: data => data === 'text' ? 'Text Box' : 'Options'
                    },
                    {data: 'sort_order'},
                    {
                        data: 'status',
                        render: (data, type, row) => `<label class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input status-change" data-id="${row.id}" type="checkbox" ${row.status === 'active' ? 'checked="checked"' : ''}/>
                        </label>`
                    },
                    {
                        data: 'id',
                        orderable: false,
                        render: id => `<a href="{{ route('admin.quizzes.add_edit_form') }}/${id}" class="btn btn-sm" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                            <button class="btn btn-sm delete-data" data-id="${id}" title="Delete"><i class="bi bi-trash"></i></button>`
                    }
                ]
            });

            $('[data-kt-quiz-table-filter="search"], [data-kt-quiz-table-filter="module"]').on('keyup change', function () {
                table.draw();
            });

            document.addEventListener('change', e => {
                if (!e.target.classList.contains('status-change')) return;
                const checkbox = e.target;
                $.post('{{ route("admin.quizzes.status_change", ":id") }}'.replace(':id', checkbox.dataset.id), {_token: '{{ csrf_token() }}'})
                    .done(() => toastr.success('Status updated successfully'))
                    .fail(() => {
                        checkbox.checked = !checkbox.checked;
                        toastr.error('Status update failed');
                    });
            });

            document.addEventListener('click', e => {
                const button = e.target.closest('.delete-data');
                if (!button) return;
                Swal.fire({text: "Are you sure you want to delete?", icon: "warning", showCancelButton: true, confirmButtonText: "Yes"})
                    .then(result => {
                        if (!result.isConfirmed) return;
                        $.post('{{ route("admin.quizzes.delete", ":id") }}'.replace(':id', button.dataset.id), {_token: '{{ csrf_token() }}'})
                            .done(() => {
                                toastr.success('Question deleted successfully');
                                table.draw(false);
                            })
                            .fail(() => toastr.error('Delete failed'));
                    });
            });

            function updateSelectedToolbar() {
                const selectedCount = document.querySelectorAll('#kt_table_quizzes .row-checkbox:checked').length;
                if (selectedCount > 0) {
                    toolbarBase.classList.add('d-none');
                    toolbarSelected.classList.remove('d-none');
                    selectedCountEl.textContent = selectedCount;
                } else {
                    toolbarBase.classList.remove('d-none');
                    toolbarSelected.classList.add('d-none');
                    selectedCountEl.textContent = '';
                }
            }

            document.addEventListener('change', e => {
                if (e.target.matches('#kt_table_quizzes [data-kt-check="true"]')) {
                    document.querySelectorAll('#kt_table_quizzes .row-checkbox').forEach(cb => cb.checked = e.target.checked);
                    updateSelectedToolbar();
                }

                if (e.target.matches('#kt_table_quizzes .row-checkbox')) {
                    updateSelectedToolbar();
                }
            });

            table.on('draw', () => {
                const checkAll = document.querySelector('#kt_table_quizzes [data-kt-check="true"]');
                if (checkAll) {
                    checkAll.checked = false;
                }
                updateSelectedToolbar();
            });

            document.querySelector('[data-kt-quiz-table-select="delete_selected"]')?.addEventListener('click', () => {
                const ids = [...document.querySelectorAll('#kt_table_quizzes .row-checkbox:checked')].map(cb => cb.value);

                if (!ids.length) {
                    Swal.fire({text: "Please select at least one.", icon: "info", confirmButtonText: "OK"});
                    return;
                }

                Swal.fire({
                    text: `Delete ${ids.length} selected question(s)?`,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, delete",
                    cancelButtonText: "Cancel"
                }).then(result => {
                    if (!result.isConfirmed) return;

                    fetch('{{ route("admin.quizzes.delete_multiple") }}', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                        body: JSON.stringify({ids})
                    })
                        .then(res => {
                            if (!res.ok) throw new Error();
                            toastr.success('Selected questions deleted successfully');
                            table.draw(false);
                        })
                        .catch(() => Swal.fire({text: "Failed to delete questions.", icon: "error", confirmButtonText: "OK"}));
                });
            });
        });
    </script>
@endpush
