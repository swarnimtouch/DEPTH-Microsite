@extends('layouts.admin')

@section('content')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Post-->
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <!--begin::Container-->
            <div id="kt_content_container" class="container-xxl">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!--begin::Card-->
                <div class="card">
                    <!--begin::Card header-->
                    <div class="card-header border-0 pt-6">
                        <!--begin::Card title-->
                        <div class="card-title">
                            <!--begin::Search-->
                            <div class="d-flex align-items-center position-relative my-1">
                                <span class="svg-icon svg-icon-1 position-absolute ms-6">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                         fill="none">
                                        <rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546" height="2" rx="1"
                                              transform="rotate(45 17.0365 15.1223)" fill="black"/>
                                        <path
                                            d="M11 19C6.55556 19 3 15.4444 3 11C3 6.55556 6.55556 3 11 3C15.4444 3 19 6.55556 19 11C19 15.4444 15.4444 19 11 19ZM11 5C7.53333 5 5 7.53333 5 11C5 14.4667 7.53333 17 11 17C14.4667 17 17 14.4667 17 11C17 7.53333 14.4667 5 11 5Z"
                                            fill="black"/>
                                    </svg>
                                </span>
                                <input type="text" data-kt-user-table-filter="search"
                                       class="form-control form-control-solid w-250px ps-14" placeholder="Search"/>
                            </div>
                            <!--end::Search-->
                        </div>
                        <!--begin::Card title-->

                        <!--begin::Card toolbar-->
                        <div class="card-toolbar">
                            <!--begin::Toolbar-->
                            <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                                <!--begin::Add user-->
                                <a href="{{ route('admin.faqs.add_edit_form') }}" class="btn btn-primary">
                                    <span class="svg-icon svg-icon-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                             viewBox="0 0 24 24" fill="none">
                                            <rect opacity="0.5" x="11.364" y="20.364" width="16" height="2" rx="1"
                                                  transform="rotate(-90 11.364 20.364)" fill="black"/>
                                            <rect x="4.36396" y="11.364" width="16" height="2" rx="1" fill="black"/>
                                        </svg>
                                    </span>
                                    Add FAQ
                                </a>
                                <!--end::Add FAQ-->
                            </div>
                            <!--end::Toolbar-->

                            <!--begin::Group actions-->
                            <div class="d-flex justify-content-end align-items-center d-none"
                                 data-kt-user-table-toolbar="selected">
                                <div class="fw-bolder me-5">
                                    <span class="me-2" data-kt-user-table-select="selected_count"></span>Selected
                                </div>
                                <button type="button" class="btn btn-danger"
                                        data-kt-user-table-select="delete_selected">
                                    Delete Selected
                                </button>
                            </div>
                            <!--end::Group actions-->
                        </div>
                        <!--end::Card toolbar-->
                    </div>
                    <!--end::Card header-->

                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <!--begin::Table-->
                        <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_faq">
                            <thead>
                            <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                                <th class="w-10px pe-2 no-sort">
                                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                        <input class="form-check-input multiple-delete-checkbox"
                                               type="checkbox"
                                               data-kt-check="true"
                                               data-kt-check-target="#kt_table_faq .row-checkbox"
                                               value="1"/>

                                    </div>
                                </th>
                                <th>Question</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                        <!--end::Table-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card-->
            </div>
            <!--end::Container-->
        </div>
        <!--end::Post-->
    </div>
@endsection


@push('scripts')
    <script src="{{asset('assets/plugins/custom/datatables/datatables.bundle.js')}}"></script>

    <script>
        "use strict";
        var KTFAQList = function () {
            var table = document.getElementById('kt_table_faq');
            const toolbarBase = document.querySelector('[data-kt-user-table-toolbar="base"]');
            const toolbarSelected = document.querySelector('[data-kt-user-table-toolbar="selected"]');
            const selectedCountEl = document.querySelector('[data-kt-user-table-select="selected_count"]');
            let faqTable;

            function initFAQTable() {
                faqTable = $('#kt_table_faq').DataTable({
                    processing: true,
                    serverSide: true,
                    searchDelay: 500,
                    ajax: {
                        url: '{{ route("admin.faqs.datatable") }}',
                        data: d => {
                            d.search = $('[data-kt-user-table-filter="search"]').val();
                        }
                    },
                    order: [[0, 'desc']],
                    columns: [
                        {
                            data: 'id',
                            orderable: false,
                            searchable: false,
                            render: id => `<div class="form-check form-check-sm form-check-custom form-check-solid"> <input class="form-check-input row-checkbox" type="checkbox" value="${id}" /> </div>`
                        },
                        {
                            data: 'que'
                        },
                        {
                            data: 'status',
                            render: (data, type, row) => {
                                return `<label class="form-check form-switch form-check-custom form-check-solid">
        <input class="form-check-input status-change" data-id="${row.id}" type="checkbox" value="${row.status === 'active' ? 1 : 0}" ${row.status === 'active' ? 'checked="checked"' : ''}/>
    </label>`
                            }
                        },
                        {
                            data: 'id',
                            orderable: false,
                            render: id => `
                    <div>
                        <a href="{{route('admin.faqs.add_edit_form')}}/${id}" class="btn btn-sm" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                        <button class="btn btn-sm delete-data" data-id="${id}" title="Delete"><i class="bi bi-trash delete-data"></i></button>
                    </div>`
                        }
                    ]
                });
            }

            $('[data-kt-user-table-filter="search"]').on('keyup', function () {
                faqTable.draw();
            });

            document.addEventListener('change', e => {
                if (e.target.classList.contains('status-change')) {

                    const checkbox = e.target;
                    const id = checkbox.dataset.id;
                    const previousState = checkbox.checked;

                    Swal.fire({
                        text: "Are you sure you want to change status?",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: "Yes"
                    }).then(result => {

                        if (!result.isConfirmed) {
                            checkbox.checked = !checkbox.checked;
                            return;
                        }

                        $.ajax({
                            url: '{{ route("admin.faqs.status_change", ":id") }}'.replace(':id', id),
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function () {
                                toastr.success('Status updated successfully!');
                            },
                            error: function () {
                                checkbox.checked = previousState;

                                Swal.fire({
                                    text: "Error updating status. Please try again.",
                                    icon: "error",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok",
                                    customClass: {
                                        confirmButton: "btn fw-bold btn-primary",
                                    }
                                });
                            }
                        });
                    });
                }

            });
            document.addEventListener('click', e => {
                if ($(e.target).parent().hasClass('delete-data')) {
                    const buttonTag = $(e.target).parent();
                    const id = buttonTag.data('id');

                    Swal.fire({
                        text: "Are you sure you want to delete?",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: "Yes"
                    }).then(result => {

                        if (!result.isConfirmed) {
                            return;
                        }

                        $.ajax({
                            url: '{{ route("admin.faqs.delete", ":id") }}'.replace(':id', id),
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function () {
                                toastr.success('Record deleted successfully!');
                                faqTable.draw(false);
                            },
                            error: function () {
                                Swal.fire({
                                    text: "Error delete record. Please try again.",
                                    icon: "error",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok",
                                    customClass: {
                                        confirmButton: "btn fw-bold btn-primary",
                                    }
                                });
                            }
                        });
                    });
                }
            });

            document
                .querySelector('[data-kt-user-table-select="delete_selected"]')
                ?.addEventListener('click', () => {

                    const ids = [...document.querySelectorAll('.row-checkbox:checked')].map(cb => cb.value);

                    if (!ids.length) {
                        Swal.fire({
                            text: "Please select at least one.",
                            icon: "info",
                            confirmButtonText: "OK"
                        });
                        return;
                    }

                    Swal.fire({
                        text: `Delete ${ids.length} selected records(s)?`,
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: "Yes, delete",
                        cancelButtonText: "Cancel"
                    }).then(result => {

                        if (!result.isConfirmed) return;

                        fetch('{{ route("admin.faqs.delete_multiple") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ids})
                        })
                            .then(res => {
                                if (!res.ok) throw new Error();
                                toastr.success('Selected records deleted successfully!');
                                faqTable.draw(false);
                                toolbarBase.classList.remove('d-none');
                                toolbarSelected.classList.add('d-none');
                                selectedCountEl.textContent = '';
                            })
                            .catch(() => {
                                Swal.fire({
                                    text: "Failed to delete record.",
                                    icon: "error",
                                    confirmButtonText: "OK"
                                });
                            });
                    });
                });
            document.addEventListener('change', e => {
                if (!e.target.classList.contains('row-checkbox') &&
                    !e.target.matches('[data-kt-check="true"]')) return;

                const selectedCount = document.querySelectorAll('.row-checkbox:checked').length;

                if (selectedCount > 0) {
                    toolbarBase.classList.add('d-none');
                    toolbarSelected.classList.remove('d-none');
                    selectedCountEl.textContent = selectedCount;
                } else {
                    toolbarBase.classList.remove('d-none');
                    toolbarSelected.classList.add('d-none');
                    selectedCountEl.textContent = '';
                }
            });
            return {
                init: function () {
                    if (!table) return;

                    initFAQTable();
                }
            }
        }();
        KTUtil.onDOMContentLoaded(function () {
            KTFAQList.init();
        });
    </script>
@endpush
