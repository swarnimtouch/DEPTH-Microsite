"use strict";
const qs = (s, p = document) => p.querySelector(s);
const qsa = (s, p = document) => [...p.querySelectorAll(s)];
const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

function openBannerPreview(url, type) {
    const modalEl = qs('#bannerPreviewModal');
    const modal = new bootstrap.Modal(modalEl);
    const body = qs('#bannerPreviewBody');
    const title = qs('#bannerPreviewTitle');

    title.textContent = 'Banner';
    body.innerHTML = '';

    if (type === 'image') {
        body.innerHTML = `<img src="${url}" class="img-fluid rounded" style="max-height:70vh">`;
    } else {
        body.innerHTML = `
                        <video controls autoplay style="width:100%;max-height:70vh" id="modalVideo">
                            <source src="${url}" type="video/mp4">
                        </video>
                    `;
    }

    modal.show();

    modalEl.addEventListener('hidden.bs.modal', () => {
        const v = qs('#modalVideo');
        if (v) {
            v.pause();
            v.currentTime = 0;
            v.src = '';
        }
    }, {once: true});
}

let bannerTable;

function initBannerTable() {

    bannerTable = $('#kt_table_users').DataTable({
        processing: true,
        serverSide: true,
        searchDelay: 500,
        ajax: {
            url: '{{ route("admin.banner.datatable") }}',
            data: d => {
                d.search = qs('[data-kt-user-table-filter="search"]').value;
                d.type = qs('[data-kt-user-table-filter="type"]').value;
                d.status = qs('[data-kt-user-table-filter="status"]').value;
            }
        },
        order: [[2, 'asc']],
        pageLength: 10,
        columns: [
            {data: null, orderable: false, defaultContent: ''},

            {
                data: 'id',
                orderable: false,
                searchable: false,
                render: id => `<div class="form-check form-check-sm form-check-custom form-check-solid"> <input class="form-check-input row-checkbox" type="checkbox" value="${id}" /> </div>`
            },
            {
                data: 'media_url',
                render: (data, type, row) => `
                    <div class="banner-preview"
                         data-url="${row.media_url}"
                         data-type="${row.type}"
                         style="width:80px;height:80px;cursor:pointer;">
                        ${
                    row.type === 'image'
                        ? `<img src="${row.media_url}" style="width:100%;height:100%;object-fit:cover">`
                        : `<video muted style="width:100%;height:100%;object-fit:cover">
                                   <source src="${row.media_url}" type="video/mp4">
                               </video>`
                }
                    </div>
                `
            },

            {data: 'title'},
            {data: 'created_at'},

            {
                data: 'status',
                render: (data, type, row) => `
                    <div class="form-check form-switch">
                        <input class="form-check-input banner-status-toggle"
                               type="checkbox"
                               data-id="${row.id}"
                               ${row.status === 'active' ? 'checked' : ''}>
                    </div>
                `
            },

            {
                data: 'id',
                orderable: false,
                searchable: false,
                render: id => `<div class="text-end">
                                                    <a href="#" class="btn btn-light btn-active-light-primary btn-sm" data-bs-toggle="dropdown"> Actions
                                                        <span class="svg-icon svg-icon-5 m-0">
                                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path d="M11.4343 12.7344L7.25 8.55005C6.83579 8.13583 6.16421 8.13584 5.75 8.55005C5.33579 8.96426 5.33579 9.63583 5.75 10.05L11.2929 15.5929C11.6834 15.9835 12.3166 15.9835 12.7071 15.5929L18.25 10.05C18.6642 9.63584 18.6642 8.96426 18.25 8.55005C17.8358 8.13584 17.1642 8.13584 16.75 8.55005L12.5657 12.7344C12.2533 13.0468 11.7467 13.0468 11.4343 12.7344Z" fill="currentColor"/> </svg>
                                                            </span>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4">
                                                        <div class="menu-item px-3">
                                                            <a href="#" class="menu-link px-3 banner-delete" data-id="${id}"> Delete </a>
                                                        </div>
                                                        <div class="menu-item px-3">
                                                            <a href="{{ route('admin.banner.create') }}/${id}" class="menu-link px-3 "> Edit </a>
                                                        </div>
                                                    </div>
                                            </div>`
            }
        ]
    });
}

document.addEventListener('click', e => {

    const preview = e.target.closest('.banner-preview');
    if (preview) {
        openBannerPreview(preview.dataset.url, preview.dataset.type);
        return;
    }

    const del = e.target.closest('.banner-delete');
    if (del) {
        e.preventDefault();
        confirmDelete(del.dataset.id, del.closest('tr'));
    }
});

document.addEventListener('mouseenter', e => {
    if (e.target.tagName === 'VIDEO') e.target.play();
}, true);

document.addEventListener('mouseleave', e => {
    if (e.target.tagName === 'VIDEO') {
        e.target.pause();
        e.target.currentTime = 0;
    }
}, true);

document.addEventListener('change', e => {
    if (!e.target.classList.contains('banner-status-toggle')) return;

    const checkbox = e.target;
    const id = checkbox.dataset.id;
    const status = checkbox.checked ? 'active' : 'inactive';

    Swal.fire({
        text: "Change banner status?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes"
    }).then(result => {

        if (!result.isConfirmed) {
            checkbox.checked = !checkbox.checked;
            return;
        }

        $.ajax({
            url: '{{ route("admin.banner.toggleStatus", ":id") }}'.replace(':id', id),
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                status
            },
            success: function () {

                if (typeof toastr !== 'undefined') {
                    toastr.success('Status updated successfully!');
                } else {
                    Swal.fire({
                        text: "Status updated successfully!",
                        icon: "success",
                        buttonsStyling: false,
                        confirmButtonText: "Ok",
                        customClass: {
                            confirmButton: "btn fw-bold btn-primary",
                        }
                    });
                }
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
});
function updateToggleState() {
    const selected = document.querySelectorAll('.row-checkbox:checked').length;
    const toggles = document.querySelectorAll('.banner-status-toggle');

    toggles.forEach(toggle => {
        toggle.disabled = selected > 1;
    });
}

function confirmDelete(id, row) {

    Swal.fire({
        text: "Delete this banner?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Delete"
    }).then(result => {

        if (!result.isConfirmed) return;

        $.ajax({
            url: '{{ route("admin.banner.delete", ":id") }}'.replace(':id', id),
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function () {
                bannerTable.draw();
                toastr.success('Banner has been deleted!');
            }
        });
    });
}

qs('[data-kt-user-table-filter="search"]')
    .addEventListener('keyup', () => bannerTable.draw());

qs('[data-kt-user-table-filter="filter"]')
    .addEventListener('click', () => bannerTable.draw());

qs('[data-kt-user-table-filter="reset"]')
    .addEventListener('click', () => {
        qs('[data-kt-user-table-filter="type"]').value = '';
        qs('[data-kt-user-table-filter="status"]').value = '';
        bannerTable.draw();
    });
document.addEventListener('change', e => {
    if (!e.target.matches('[data-kt-check="true"]')) return;

    const checked = e.target.checked;
    qsa('.row-checkbox').forEach(cb => cb.checked = checked);
});

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

            fetch('{{ route("admin.banner.deleteMultiple") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({ids})
            })
                .then(res => {
                    if (!res.ok) throw new Error();
                    Swal.fire({
                        text: "Selected banners deleted successfully.",
                        icon: "success",
                        confirmButtonText: "OK"
                    });
                    bannerTable.draw(false);
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
    updateToggleState();

});
KTUtil.onDOMContentLoaded(() => {
    initBannerTable();
});

