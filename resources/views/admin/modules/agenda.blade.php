@extends('layouts.admin')

@section('content')
    @php
        $storedAgendaRows = collect($module->speakers ?: [])->map(function ($row) {
            if (isset($row['name']) && !isset($row['names'])) {
                return [
                    'type' => 'person',
                    'title' => '',
                    'role' => $row['designation'] ?? 'Speaker',
                    'names' => $row['name'] ?? '',
                ];
            }

            return $row;
        })->values()->all();
        $agendaRows = old('agenda', $storedAgendaRows ?: [
            ['type' => 'section', 'title' => 'Lecture : ', 'role' => '', 'names' => ''],
            ['type' => 'person', 'title' => '', 'role' => 'Chairs', 'names' => ''],
            ['type' => 'person', 'title' => '', 'role' => 'Speaker', 'names' => ''],
        ]);
        $archiveSegments = old('archive_segments', $module->archive_segments ?: [
            ['topic' => '', 'youtube_url' => $module->video_url, 'start_time' => ''],
        ]);
    @endphp

    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.modules.agenda_save', $module->id) }}" id="episode_agenda_form">
                    @csrf
                    @method('PUT')

                    <div class="card mb-8">
                        <div class="card-header">
                            <div class="card-title">
                                <div>
                                    <div class="fs-3 fw-bolder">Set Agenda</div>
                                    <div class="text-muted fs-7">{{ $module->title }}</div>
                                </div>
                            </div>
                            <div class="card-toolbar">
                                <button type="button" class="btn btn-light-primary btn-sm me-2" id="addSectionRow">Add Topic Row</button>
                                <button type="button" class="btn btn-light-primary btn-sm" id="addPersonRow">Add Person Row</button>
                            </div>
                        </div>

                        <div class="card-body border-top p-9">
                            <div class="episode-agenda-preview mb-8">
                                <div class="episode-agenda-preview-title">Topic : {{ $module->title }}</div>
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle mb-0">
                                        <tbody id="agendaPreviewRows"></tbody>
                                    </table>
                                </div>
                            </div>

                            <div id="agendaRows">
                                @foreach($agendaRows as $index => $row)
                                    <div class="agenda-row border rounded p-4 mb-4" data-type="{{ $row['type'] ?? 'person' }}">
                                        <div class="row g-3 align-items-end">
                                            <div class="col-md-2">
                                                <label class="form-label">Row Type</label>
                                                <select name="agenda[{{ $index }}][type]" class="form-select form-select-solid agenda-type">
                                                    <option value="section" {{ ($row['type'] ?? '') === 'section' ? 'selected' : '' }}>Topic</option>
                                                    <option value="person" {{ ($row['type'] ?? 'person') === 'person' ? 'selected' : '' }}>Person</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 agenda-title-wrap">
                                                <label class="form-label">Topic / Session Title</label>
                                                <input type="text" name="agenda[{{ $index }}][title]" value="{{ $row['title'] ?? '' }}" class="form-control form-control-solid agenda-title" placeholder="Lecture : Dilemmas in Endometriosis">
                                            </div>
                                            <div class="col-md-2 agenda-role-wrap">
                                                <label class="form-label">Role</label>
                                                <input type="text" name="agenda[{{ $index }}][role]" value="{{ $row['role'] ?? '' }}" class="form-control form-control-solid agenda-role" placeholder="Chairs">
                                            </div>
                                            <div class="col-md-3 agenda-names-wrap">
                                                <label class="form-label">Names</label>
                                                <textarea name="agenda[{{ $index }}][names]" rows="1" class="form-control form-control-solid agenda-names" placeholder="Name, Name">{{ $row['names'] ?? '' }}</textarea>
                                            </div>
                                            <div class="col-md-1">
                                                <button type="button" class="btn btn-light-danger remove-agenda-row w-100">X</button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                <div>
                                    <div class="fs-3 fw-bolder">Archival Topic Videos</div>
                                    <div class="text-muted fs-7">Shown after the delegate marks this chapter complete.</div>
                                </div>
                            </div>
                            <div class="card-toolbar">
                                <button type="button" class="btn btn-light-primary btn-sm" id="addArchiveSegment">Add Topic Video</button>
                            </div>
                        </div>
                        <div class="card-body border-top p-9">
                            <div id="archiveRows">
                                @foreach($archiveSegments as $index => $segment)
                                    <div class="archive-row row g-3 mb-4 align-items-end border rounded p-4">
                                        <div class="col-md-4">
                                            <label class="form-label">Topic</label>
                                            <input type="text" name="archive_segments[{{ $index }}][topic]" value="{{ $segment['topic'] ?? '' }}" class="form-control form-control-solid" placeholder="Medical Management">
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label">YouTube Link</label>
                                            <input type="url" name="archive_segments[{{ $index }}][youtube_url]" value="{{ $segment['youtube_url'] ?? '' }}" class="form-control form-control-solid" placeholder="https://www.youtube.com/watch?v=...">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Start Time</label>
                                            <input type="text" name="archive_segments[{{ $index }}][start_time]" value="{{ $segment['start_time'] ?? '' }}" class="form-control form-control-solid" placeholder="12:30">
                                        </div>
                                        <div class="col-md-1">
                                            <button type="button" class="btn btn-light-danger remove-archive-row w-100">X</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-end py-6 px-9">
                            <a href="{{ route('admin.modules') }}" class="btn btn-light btn-active-light-primary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save Agenda</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        "use strict";
        KTUtil.onDOMContentLoaded(function () {
            const agendaRows = document.getElementById('agendaRows');
            const archiveRows = document.getElementById('archiveRows');

            function refreshNames() {
                agendaRows.querySelectorAll('.agenda-row').forEach((row, index) => {
                    row.querySelectorAll('input, select, textarea').forEach(input => {
                        input.name = input.name.replace(/agenda\[\d+]/, `agenda[${index}]`);
                    });
                });
                archiveRows.querySelectorAll('.archive-row').forEach((row, index) => {
                    row.querySelectorAll('input').forEach(input => {
                        input.name = input.name.replace(/archive_segments\[\d+]/, `archive_segments[${index}]`);
                    });
                });
            }

            function renderPreview() {
                const preview = document.getElementById('agendaPreviewRows');
                preview.innerHTML = '';
                agendaRows.querySelectorAll('.agenda-row').forEach(row => {
                    const type = row.querySelector('.agenda-type').value;
                    const title = row.querySelector('.agenda-title').value.trim();
                    const role = row.querySelector('.agenda-role').value.trim();
                    const names = row.querySelector('.agenda-names').value.trim();
                    if (!title && !role && !names) return;

                    if (type === 'section') {
                        preview.insertAdjacentHTML('beforeend', `<tr class="agenda-preview-section"><td colspan="2">${title || '&nbsp;'}</td></tr>`);
                    } else {
                        preview.insertAdjacentHTML('beforeend', `<tr><th>${role || '&nbsp;'}</th><td>${names || '&nbsp;'}</td></tr>`);
                    }
                });
            }

            function agendaRow(type) {
                const index = agendaRows.querySelectorAll('.agenda-row').length;
                return `<div class="agenda-row border rounded p-4 mb-4" data-type="${type}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label">Row Type</label>
                            <select name="agenda[${index}][type]" class="form-select form-select-solid agenda-type">
                                <option value="section" ${type === 'section' ? 'selected' : ''}>Topic</option>
                                <option value="person" ${type === 'person' ? 'selected' : ''}>Person</option>
                            </select>
                        </div>
                        <div class="col-md-4 agenda-title-wrap">
                            <label class="form-label">Topic / Session Title</label>
                            <input type="text" name="agenda[${index}][title]" class="form-control form-control-solid agenda-title" placeholder="Point of View : Surgical Management">
                        </div>
                        <div class="col-md-2 agenda-role-wrap">
                            <label class="form-label">Role</label>
                            <input type="text" name="agenda[${index}][role]" class="form-control form-control-solid agenda-role" placeholder="Moderator">
                        </div>
                        <div class="col-md-3 agenda-names-wrap">
                            <label class="form-label">Names</label>
                            <textarea name="agenda[${index}][names]" rows="1" class="form-control form-control-solid agenda-names" placeholder="Name, Name"></textarea>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-light-danger remove-agenda-row w-100">X</button>
                        </div>
                    </div>
                </div>`;
            }

            document.getElementById('addSectionRow').addEventListener('click', () => {
                agendaRows.insertAdjacentHTML('beforeend', agendaRow('section'));
                renderPreview();
            });

            document.getElementById('addPersonRow').addEventListener('click', () => {
                agendaRows.insertAdjacentHTML('beforeend', agendaRow('person'));
                renderPreview();
            });

            document.getElementById('addArchiveSegment').addEventListener('click', () => {
                const index = archiveRows.querySelectorAll('.archive-row').length;
                archiveRows.insertAdjacentHTML('beforeend', `<div class="archive-row row g-3 mb-4 align-items-end border rounded p-4">
                    <div class="col-md-4">
                        <label class="form-label">Topic</label>
                        <input type="text" name="archive_segments[${index}][topic]" class="form-control form-control-solid" placeholder="Topic name">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">YouTube Link</label>
                        <input type="url" name="archive_segments[${index}][youtube_url]" class="form-control form-control-solid" placeholder="https://www.youtube.com/watch?v=...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Start Time</label>
                        <input type="text" name="archive_segments[${index}][start_time]" class="form-control form-control-solid" placeholder="12:30">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-light-danger remove-archive-row w-100">X</button>
                    </div>
                </div>`);
            });

            document.addEventListener('click', e => {
                if (e.target.classList.contains('remove-agenda-row')) {
                    e.target.closest('.agenda-row').remove();
                    refreshNames();
                    renderPreview();
                }
                if (e.target.classList.contains('remove-archive-row')) {
                    e.target.closest('.archive-row').remove();
                    refreshNames();
                }
            });

            agendaRows.addEventListener('input', renderPreview);
            agendaRows.addEventListener('change', renderPreview);
            renderPreview();
        });
    </script>
@endpush

@push('styles')
    <style>
        .episode-agenda-preview {
            border: 1px solid #e4e6ef;
            border-radius: 10px;
            overflow: hidden;
        }

        .episode-agenda-preview-title {
            background: #7d74bd;
            color: #ffffff;
            font-size: 22px;
            font-weight: 700;
            padding: 16px 20px;
            text-align: center;
        }

        .episode-agenda-preview table th {
            width: 24%;
            color: #111827;
            font-weight: 700;
            text-align: center;
        }

        .episode-agenda-preview table td {
            color: #111827;
            font-weight: 600;
            text-align: center;
        }

        .agenda-preview-section td {
            background: #eeeeF8;
            font-weight: 800;
        }
    </style>
@endpush
