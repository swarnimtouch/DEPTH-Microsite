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
                                       class="form-control form-control-solid w-250px ps-14" placeholder="Search user"/>
                            </div>
                            <!--end::Search-->
                        </div>
                        <!--begin::Card title-->

                        <!--begin::Card toolbar-->
                        <div class="card-toolbar">
                            <!--begin::Toolbar-->
                            <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                                <!--begin::Filter-->

                                <!--begin::Menu-->
                                <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true"
                                     id="filter-menu">

                                    <div id="dynamic-filters"></div>

                                </div>
                                <!--end::Menu-->
                                <!--end::Filter-->

                                <!--begin::Export-->
                                <button type="button" class="btn btn-light-primary me-3" id="export-btn">
                                    <span class="svg-icon svg-icon-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                             viewBox="0 0 24 24" fill="none">
                                            <rect opacity="0.3" x="12.75" y="4.25" width="12" height="2" rx="1"
                                                  transform="rotate(90 12.75 4.25)" fill="black"/>
                                            <path
                                                d="M12.0573 6.11875L13.5203 7.87435C13.9121 8.34457 14.6232 8.37683 15.056 7.94401C15.4457 7.5543 15.4641 6.92836 15.0979 6.51643L12.4974 3.59084C12.0996 3.14332 11.4004 3.14332 11.0026 3.59084L8.40206 6.51643C8.0359 6.92836 8.0543 7.5543 8.44401 7.94401C8.87683 8.37683 9.58785 8.34458 9.9797 7.87435L11.4427 6.11875C11.6026 5.92684 11.8974 5.92684 12.0573 6.11875Z"
                                                fill="black"/>
                                            <path
                                                d="M18.75 8.25H17.75C17.1977 8.25 16.75 8.69772 16.75 9.25C16.75 9.80228 17.1977 10.25 17.75 10.25C18.3023 10.25 18.75 10.6977 18.75 11.25V18.25C18.75 18.8023 18.3023 19.25 17.75 19.25H5.75C5.19772 19.25 4.75 18.8023 4.75 18.25V11.25C4.75 10.6977 5.19771 10.25 5.75 10.25C6.30229 10.25 6.75 9.80228 6.75 9.25C6.75 8.69772 6.30229 8.25 5.75 8.25H4.75C3.64543 8.25 2.75 9.14543 2.75 10.25V19.25C2.75 20.3546 3.64543 21.25 4.75 21.25H18.75C19.8546 21.25 20.75 20.3546 20.75 19.25V10.25C20.75 9.14543 19.8546 8.25 18.75 8.25Z"
                                                fill="#C4C4C4"/>
                                        </svg>
                                    </span>
                                    Export
                                </button>
                                <!--end::Export-->

                                <!--begin::Add user-->
                                <a href="{{ route('admin.user.create') }}" class="btn btn-primary">
                                    <span class="svg-icon svg-icon-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                             viewBox="0 0 24 24" fill="none">
                                            <rect opacity="0.5" x="11.364" y="20.364" width="16" height="2" rx="1"
                                                  transform="rotate(-90 11.364 20.364)" fill="black"/>
                                            <rect x="4.36396" y="11.364" width="16" height="2" rx="1" fill="black"/>
                                        </svg>
                                    </span>
                                    Add User
                                </a>
                                <!--end::Add user-->
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
                        <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_users">
                            <thead>
                            <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                                <th></th>
                                <th class="w-10px pe-2">
                                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               data-kt-check="true"
                                               data-kt-check-target="#kt_table_users .row-checkbox"
                                               value="1"/>

                                    </div>
                                </th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Mobile</th>
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
        const qsa = (s, p = document) => [...p.querySelectorAll(s)];

        var KTUsersList = function () {
            var table = document.getElementById('kt_table_users');
            let userTable;


            function initUserTable() {
                userTable = $('#kt_table_users').DataTable({
                    processing: true,
                    serverSide: true,
                    searchDelay: 500,
                    ajax: {
                        url: '{{ route("admin.user.datatable") }}',
                        data: d => {
                            d.search = $('[data-kt-user-table-filter="search"]').val();
                        }
                    },
                    order: [[2, 'asc']],
                    columns: [
                        {data: null, orderable: false, defaultContent: ''},
                        {
                            data: 'id',
                            orderable: false,
                            searchable: false,
                            render: id => `<div class="form-check form-check-sm form-check-custom form-check-solid"> <input class="form-check-input row-checkbox" type="checkbox" value="${id}" /> </div>`
                        },
                        {
                            data:'name'
                        },
                        {
                            data:'email'
                        },
                        {
                            data:'mobile'
                        },
                        {
                            data: 'id',
                            orderable: false,
                            render: id => `
                    <div>
                        <a href="/admin/user/${id}" class="btn btn-sm" title="View"><i class="bi bi-eye"></i></a>
                        <a href="/admin/user/${id}/edit" class="btn btn-sm" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                        <button class="btn btn-sm delete-user" data-id="${id}" title="Delete"><i class="bi bi-trash"></i></button>
                    </div>`
                        }
                    ]
                });
            }

            $('[data-kt-user-table-filter="search"]').on('keyup', function () {
                userTable.draw();
            });

            $(document).on('click', '.delete-user', function () {
                deleteUser($(this).data('id'));
            });

            // Handle export
            var handleExport = function () {
                const exportBtn = document.getElementById('export-btn');

                if (exportBtn) {
                    exportBtn.addEventListener('click', function () {
                        const originalHTML = exportBtn.innerHTML;
                        exportBtn.disabled = true;
                        exportBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Exporting...';

                        let csv = '';

                        // Add headers (excluding city, state, country)
                        const headers = dynamicFields.map(f => f.label);
                        csv += headers.join(',') + '\n';

                        // Add rows
                        const rows = document.querySelectorAll('#kt_table_users tbody tr');
                        rows.forEach(row => {
                            const cells = row.querySelectorAll('td');
                            if (cells.length > 1) {
                                let rowData = [];
                                for (let i = 2; i < cells.length - 1; i++) {
                                    const text = cells[i].innerText.trim().replace(/\n/g, ' ');
                                    rowData.push(`"${text}"`);
                                }
                                csv += rowData.join(',') + '\n';
                            }
                        });

                        // Download
                        const blob = new Blob([csv], {type: 'text/csv'});
                        const url = window.URL.createObjectURL(blob);
                        const link = document.createElement('a');
                        link.setAttribute('href', url);
                        link.setAttribute('download', 'users_export.csv');
                        link.style.visibility = 'hidden';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);

                        setTimeout(() => {
                            exportBtn.disabled = false;
                            exportBtn.innerHTML = originalHTML;

                            Swal.fire({
                                text: "Users exported successfully!",
                                icon: "success",
                                buttonsStyling: false,
                                confirmButtonText: "Ok, got it!",
                                customClass: {
                                    confirmButton: "btn fw-bold btn-primary",
                                }
                            });
                        }, 500);
                    });
                }
            }


            document
                .querySelector('[data-kt-user-table-select="delete_selected"]')
                ?.addEventListener('click', () => {

                    const ids = qsa('.row-checkbox:checked')
                        .map(cb => cb.value);

                    if (!ids.length) {
                        Swal.fire({
                            text: "Please select at least one banner.",
                            icon: "info",
                            confirmButtonText: "OK"
                        });
                        return;
                    }

                    Swal.fire({
                        text: `Delete ${ids.length} selected banner(s)?`,
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: "Yes, delete",
                        cancelButtonText: "Cancel"
                    }).then(result => {

                        if (!result.isConfirmed) return;

                        fetch('{{ route("admin.user.deleteMultiple") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ids})
                        })
                            .then(res => {
                                if (!res.ok) throw new Error();
                                Swal.fire({
                                    text: "Selected Users deleted successfully.",
                                    icon: "success",
                                    confirmButtonText: "OK"
                                });
                                userTable.draw(false);
                            })
                            .catch(() => {
                                Swal.fire({
                                    text: "Failed to delete banners.",
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

                const toolbarBase = document.querySelector('[data-kt-user-table-toolbar="base"]');
                const toolbarSelected = document.querySelector('[data-kt-user-table-toolbar="selected"]');
                const selectedCountEl = document.querySelector('[data-kt-user-table-select="selected_count"]');

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

            function deleteUser(id) {
                Swal.fire({
                    text: "Are you sure you want to delete this user?",
                    icon: "warning",
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: "Yes, delete it!",
                    cancelButtonText: "No, cancel",
                    customClass: {
                        confirmButton: "btn fw-bold btn-danger",
                        cancelButton: "btn fw-bold btn-active-light-primary"
                    }
                }).then(function (result) {
                    if (result.value) {
                        $.ajax({
                            url: '{{ route("admin.user.destroy", ":id") }}'.replace(':id', id),
                            method: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function (response) {
                                Swal.fire({
                                    text: "User has been deleted!",
                                    icon: "success",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok, got it!",
                                    customClass: {
                                        confirmButton: "btn fw-bold btn-primary",
                                    }
                                }).then(function () {
                                    userTable.draw(false);
                                });
                            },
                            error: function (xhr) {
                                Swal.fire({
                                    text: "Error deleting user. Please try again.",
                                    icon: "error",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok, got it!",
                                    customClass: {
                                        confirmButton: "btn fw-bold btn-primary",
                                    }
                                });
                            }
                        });
                    }
                });
            }

            return {
                init: function () {
                    if (!table) return;

                    initUserTable();
                    handleExport();
                }
            }
        }();
        KTUtil.onDOMContentLoaded(function () {
            KTUsersList.init();
        });
    </script>
@endpush
