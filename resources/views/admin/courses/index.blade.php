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
                        <div class="card-title fs-3 fw-bolder">Programme</div>
                        <div class="card-toolbar">
                            <div class="d-flex justify-content-end" data-kt-course-table-toolbar="base">
                                <a href="{{ route('admin.courses.add_edit_form') }}" class="btn btn-primary">Add Programme</a>
                            </div>

                            <div class="d-flex justify-content-end align-items-center d-none" data-kt-course-table-toolbar="selected">
                                <div class="fw-bolder me-5">
                                    <span class="me-2" data-kt-course-table-select="selected_count"></span>Selected
                                </div>
                                <button type="button" class="btn btn-danger" data-kt-course-table-select="delete_selected">
                                    Delete Selected
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_courses">
                            <thead>
                            <tr class="text-start text-muted fw-bolder fs-7 text-uppercase">
                                <th class="w-10px pe-2">
                                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                        <input class="form-check-input multiple-delete-checkbox" type="checkbox" data-kt-check="true" data-kt-check-target="#kt_table_courses .row-checkbox" value="1"/>
                                    </div>
                                </th>
                                <th>Title</th>
                                <th>Subtitle</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($courses as $course)
                                <tr>
                                    <td>
                                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                                            <input class="form-check-input row-checkbox" type="checkbox" value="{{ $course->id }}">
                                        </div>
                                    </td>
                                    <td class="fw-bold">{{ $course->title }}</td>
                                    <td>{{ $course->subtitle }}</td>
                                    <td>
                                        <label class="form-check form-switch form-check-custom form-check-solid">
                                            <input class="form-check-input status-change" data-id="{{ $course->id }}" type="checkbox" {{ $course->status === 'active' ? 'checked' : '' }}>
                                        </label>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.courses.add_edit_form', $course->id) }}" class="btn btn-sm"><i class="bi bi-pencil-fill"></i></a>
                                        <button type="button" class="btn btn-sm delete-data" data-id="{{ $course->id }}"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('change', e => {
            if (!e.target.classList.contains('status-change')) return;
            $.post('{{ route("admin.courses.status_change", ":id") }}'.replace(':id', e.target.dataset.id), {_token: '{{ csrf_token() }}'})
                .done(() => {
                    toastr.success('Programme status updated');
                    setTimeout(() => window.location.reload(), 500);
                })
                .fail(() => toastr.error('Status update failed'));
        });

        const toolbarBase = document.querySelector('[data-kt-course-table-toolbar="base"]');
        const toolbarSelected = document.querySelector('[data-kt-course-table-toolbar="selected"]');
        const selectedCountEl = document.querySelector('[data-kt-course-table-select="selected_count"]');

        function updateSelectedToolbar() {
            const selectedCount = document.querySelectorAll('#kt_table_courses .row-checkbox:checked').length;
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
            if (e.target.matches('[data-kt-check="true"]')) {
                document.querySelectorAll('#kt_table_courses .row-checkbox').forEach(cb => cb.checked = e.target.checked);
                updateSelectedToolbar();
            }

            if (e.target.classList.contains('row-checkbox')) {
                updateSelectedToolbar();
            }
        });

        document.addEventListener('click', e => {
            const button = e.target.closest('.delete-data');
            if (!button) return;

            Swal.fire({text: "Are you sure you want to delete?", icon: "warning", showCancelButton: true, confirmButtonText: "Yes"})
                .then(result => {
                    if (!result.isConfirmed) return;
                    $.post('{{ route("admin.courses.delete", ":id") }}'.replace(':id', button.dataset.id), {_token: '{{ csrf_token() }}'})
                        .done(() => {
                            toastr.success('Programme deleted successfully');
                            button.closest('tr').remove();
                            updateSelectedToolbar();
                        })
                        .fail(() => toastr.error('Delete failed'));
                });
        });

        document.querySelector('[data-kt-course-table-select="delete_selected"]')?.addEventListener('click', () => {
            const ids = [...document.querySelectorAll('#kt_table_courses .row-checkbox:checked')].map(cb => cb.value);

            if (!ids.length) {
                Swal.fire({text: "Please select at least one.", icon: "info", confirmButtonText: "OK"});
                return;
            }

            Swal.fire({
                text: `Delete ${ids.length} selected programme?`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, delete",
                cancelButtonText: "Cancel"
            }).then(result => {
                if (!result.isConfirmed) return;

                fetch('{{ route("admin.courses.delete_multiple") }}', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                    body: JSON.stringify({ids})
                })
                    .then(res => {
                        if (!res.ok) throw new Error();
                        ids.forEach(id => document.querySelector(`#kt_table_courses .row-checkbox[value="${id}"]`)?.closest('tr')?.remove());
                        toastr.success('Selected programme deleted successfully');
                        updateSelectedToolbar();
                    })
                    .catch(() => Swal.fire({text: "Failed to delete programme.", icon: "error", confirmButtonText: "OK"}));
            });
        });
    </script>
@endpush
