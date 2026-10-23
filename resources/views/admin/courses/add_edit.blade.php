@extends('layouts.admin')

@section('content')
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
                <div class="card">
                    <div class="card-header">
                        <div class="card-title fs-3 fw-bolder">{{ $course->id ? 'Edit Programme' : 'Add Programme' }}</div>
                    </div>
                    <form method="POST" action="{{ route('admin.courses.save', $course->id ?? null) }}" enctype="multipart/form-data">
                        @csrf
                        @if($course->id)
                            @method('PUT')
                        @endif
                        <div class="card-body border-top p-9">
                            <div class="row mb-6">
                                <label class="col-lg-3 col-form-label fw-bold fs-6"><span class="required">Title</span></label>
                                <div class="col-lg-9">
                                    <input type="text" name="title" value="{{ old('title', $course->title) }}" class="form-control form-control-lg form-control-solid">
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-3 col-form-label fw-bold fs-6">Subtitle</label>
                                <div class="col-lg-9">
                                    <input type="text" name="subtitle" value="{{ old('subtitle', $course->subtitle) }}" class="form-control form-control-lg form-control-solid">
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-3 col-form-label fw-bold fs-6"><span class="required">About Programme</span></label>
                                <div class="col-lg-9">
                                    <textarea name="about" rows="8" class="form-control form-control-lg form-control-solid">{{ old('about', $course->about) }}</textarea>
                                </div>
                            </div>
                            @php
                                $speakerRows = old('speakers', $course->speakers ?: [['type' => 'speaker', 'image' => '', 'name' => '', 'designation' => '', 'hospital' => '']]);
                                $personTypes = ['speaker' => 'Speaker', 'convenor' => 'Convenor', 'coordinator' => 'Coordinator'];
                            @endphp
                            <div class="row mb-6">
                                <label class="col-lg-3 col-form-label fw-bold fs-6">Programme People</label>
                                <div class="col-lg-9">
                                    <div id="course-speakers-wrapper">
                                        @foreach($speakerRows as $index => $speaker)
                                            <div class="course-speaker-row border rounded p-4 mb-4">
                                                <div class="row g-3">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Type</label>
                                                        <select name="speakers[{{ $index }}][type]" class="form-select form-select-solid">
                                                            @foreach($personTypes as $value => $label)
                                                                <option value="{{ $value }}" {{ ($speaker['type'] ?? 'speaker') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label">Name</label>
                                                        <input type="text" name="speakers[{{ $index }}][name]" value="{{ $speaker['name'] ?? '' }}" class="form-control form-control-solid" placeholder="Speaker name">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label">Designation</label>
                                                        <input type="text" name="speakers[{{ $index }}][designation]" value="{{ $speaker['designation'] ?? '' }}" class="form-control form-control-solid" placeholder="Designation">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label">Hospital</label>
                                                        <input type="text" name="speakers[{{ $index }}][hospital]" value="{{ $speaker['hospital'] ?? '' }}" class="form-control form-control-solid" placeholder="Hospital">
                                                    </div>
                                                    <div class="col-md-1 d-flex align-items-end">
                                                        <button type="button" class="btn btn-light-danger remove-course-speaker w-100">X</button>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Image</label>
                                                        @if(!empty($speaker['image']))
                                                            <div class="mb-2">
                                                                <img src="{{ asset('storage/' . $speaker['image']) }}" class="rounded border" width="72" height="72" style="object-fit: cover;" alt="{{ $speaker['name'] ?? 'Programme person' }}">
                                                            </div>
                                                        @endif
                                                        <input type="hidden" name="speakers[{{ $index }}][image]" value="{{ $speaker['image'] ?? '' }}">
                                                        <input type="file" name="speaker_images[{{ $index }}]" class="form-control form-control-solid" accept=".jpg,.jpeg,.png,.webp">
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <button type="button" class="btn btn-light-primary" id="add-course-speaker">Add Person</button>
                                    <div class="form-text">Add speakers, convenors and coordinators with image. If no programme people are added, website will use chapter speakers.</div>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-3 col-form-label fw-bold fs-6">Agenda</label>
                                <div class="col-lg-9">
                                    <div class="alert alert-info mb-0">
                                        Agenda is generated dynamically from chapters in this programme.
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-3 col-form-label fw-bold fs-6"><span class="required">Status</span></label>
                                <div class="col-lg-9">
                                    <select name="status" class="form-select form-select-lg form-select-solid">
                                        <option value="inactive" {{ old('status', $course->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        <option value="active" {{ old('status', $course->status) === 'active' ? 'selected' : '' }}>Active</option>
                                    </select>
                                    <div class="form-text">Only one programme can be active at a time.</div>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-3 col-form-label fw-bold fs-6"><span class="required">Microsite Access</span></label>
                                <div class="col-lg-9">
                                    <select name="microsite_access_enabled" class="form-select form-select-lg form-select-solid">
                                        <option value="0" @selected(!(bool) old('microsite_access_enabled', $course->microsite_access_enabled))>No — registration only</option>
                                        <option value="1" @selected((bool) old('microsite_access_enabled', $course->microsite_access_enabled))>Yes — allow participant access</option>
                                    </select>
                                    <div class="form-text">Select No to keep registration open while blocking login, dashboard, chapters and participant resources.</div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-end py-6 px-9">
                            <a href="{{ route('admin.courses') }}" class="btn btn-light me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const wrapper = document.getElementById('course-speakers-wrapper');
            const addButton = document.getElementById('add-course-speaker');

            function refreshIndexes() {
                wrapper.querySelectorAll('.course-speaker-row').forEach((row, index) => {
                    row.querySelectorAll('input, select').forEach(input => {
                        input.name = input.name.replace(/speakers\[\d+]/, `speakers[${index}]`);
                        input.name = input.name.replace(/speaker_images\[\d+]/, `speaker_images[${index}]`);
                    });
                });
            }

            addButton.addEventListener('click', function () {
                const index = wrapper.querySelectorAll('.course-speaker-row').length;
                wrapper.insertAdjacentHTML('beforeend', `
                    <div class="course-speaker-row border rounded p-4 mb-4">
                        <div class="row g-3">
                            <div class="col-md-2">
                                <label class="form-label">Type</label>
                                <select name="speakers[${index}][type]" class="form-select form-select-solid">
                                    <option value="speaker">Speaker</option>
                                    <option value="convenor">Convenor</option>
                                    <option value="coordinator">Coordinator</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Name</label>
                                <input type="text" name="speakers[${index}][name]" class="form-control form-control-solid" placeholder="Speaker name">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Designation</label>
                                <input type="text" name="speakers[${index}][designation]" class="form-control form-control-solid" placeholder="Designation">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Hospital</label>
                                <input type="text" name="speakers[${index}][hospital]" class="form-control form-control-solid" placeholder="Hospital">
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="button" class="btn btn-light-danger remove-course-speaker w-100">X</button>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Image</label>
                                <input type="hidden" name="speakers[${index}][image]" value="">
                                <input type="file" name="speaker_images[${index}]" class="form-control form-control-solid" accept=".jpg,.jpeg,.png,.webp">
                            </div>
                        </div>
                    </div>
                `);
            });

            wrapper.addEventListener('click', function (event) {
                if (!event.target.classList.contains('remove-course-speaker')) {
                    return;
                }

                if (wrapper.querySelectorAll('.course-speaker-row').length === 1) {
                    const row = event.target.closest('.course-speaker-row');
                    row.querySelectorAll('input').forEach(input => input.value = '');
                    row.querySelector('select').value = 'speaker';
                    return;
                }

                event.target.closest('.course-speaker-row').remove();
                refreshIndexes();
            });
        });
    </script>
@endpush
