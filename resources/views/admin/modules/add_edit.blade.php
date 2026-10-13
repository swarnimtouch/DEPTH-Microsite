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
                        <div class="card-title fs-3 fw-bolder">{{ $module->id ? 'Edit Chapter' : 'Add Chapter' }}</div>
                    </div>

                    <form method="POST" action="{{ route('admin.modules.save', $module->id ?? null) }}" id="kt_module_form" enctype="multipart/form-data">
                        @csrf
                        @if($module->id)
                            @method('PUT')
                        @endif

                        <div class="card-body border-top p-9">
                            <div class="row mb-6">
                                <label class="col-lg-3 col-form-label fw-bold fs-6"><span class="required">Title</span></label>
                                <div class="col-lg-9">
                                    <input type="text" name="title" value="{{ old('title', $module->title) }}" class="form-control form-control-lg form-control-solid" placeholder="Chapter title"/>
                                </div>
                            </div>

                            <div class="row mb-6">
                                <label class="col-lg-3 col-form-label fw-bold fs-6"><span class="required">Programme</span></label>
                                <div class="col-lg-9">
                                    <select name="course_id" class="form-select form-select-lg form-select-solid">
                                        <option value="">Select Programme</option>
                                        @foreach($courses as $course)
                                            <option value="{{ $course->id }}" {{ old('course_id', $module->course_id) == $course->id ? 'selected' : '' }}>{{ $course->title }} ({{ ucfirst($course->status) }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-6">
                                <label class="col-lg-3 col-form-label fw-bold fs-6">Venue</label>
                                <div class="col-lg-9">
                                    <input type="text" name="venue" value="{{ old('venue', $module->venue) }}" class="form-control form-control-lg form-control-solid" placeholder="City, Country" />
                                </div>
                            </div>

                            <div class="row mb-6">
                                <label class="col-lg-3 col-form-label fw-bold fs-6">Slug</label>
                                <div class="col-lg-9">
                                    <input type="text" name="slug" value="{{ old('slug', $module->slug) }}" class="form-control form-control-lg form-control-solid" placeholder="Leave blank to auto-generate" readonly/>
                                </div>
                            </div>

                            @php
                                $defaultSpeaker = [
                                    'name' => $module->speaker_name,
                                    'role' => 'Speaker',
                                    'current_position' => $module->speaker_designation,
                                    'designation' => $module->speaker_designation,
                                    'hospital' => $module->speaker_hospital,
                                    'country' => '',
                                    'city' => '',
                                    'bio' => '',
                                    'expertise' => '',
                                    'session_title' => $module->title,
                                    'profile_resource_url' => '',
                                    'image' => null,
                                ];
                                $speakerRows = old('speakers', $module->speakers ?: [$defaultSpeaker]);
                            @endphp
                            <div class="row mb-8">
                                <label class="col-lg-3 col-form-label fw-bold fs-6"><span class="required">Speakers</span></label>
                                <div class="col-lg-9">
                                    <div id="speakerRows">
                                        @foreach($speakerRows as $index => $speaker)
                                            <div class="speaker-row border rounded p-4 mb-4" data-speaker-index="{{ $index }}">
                                                <div class="row g-3">
                                                    <div class="col-md-5"><label class="form-label">Speaker Name</label><input type="text" name="speakers[{{ $index }}][name]" value="{{ $speaker['name'] ?? '' }}" class="form-control form-control-solid" placeholder="Prof. Speaker Name" required></div>
                                                    <div class="col-md-3"><label class="form-label">Role</label><input type="text" name="speakers[{{ $index }}][role]" value="{{ $speaker['role'] ?? 'Speaker' }}" class="form-control form-control-solid" placeholder="Speaker"></div>
                                                    <div class="col-md-3"><label class="form-label">Designation</label><input type="text" name="speakers[{{ $index }}][designation]" value="{{ $speaker['designation'] ?? '' }}" class="form-control form-control-solid" placeholder="Professor of Psychiatry" required></div>
                                                    <div class="col-md-1 d-flex align-items-end"><button type="button" class="btn btn-light-danger remove-speaker-row w-100">X</button></div>
                                                    <div class="col-md-6"><label class="form-label">Institution / Hospital</label><input type="text" name="speakers[{{ $index }}][hospital]" value="{{ $speaker['hospital'] ?? '' }}" class="form-control form-control-solid" placeholder="Medical University / Hospital"></div>
                                                    <div class="col-md-6"><label class="form-label">Areas of Expertise</label><input type="text" name="speakers[{{ $index }}][expertise]" value="{{ is_array($speaker['expertise'] ?? null) ? implode(', ', $speaker['expertise']) : ($speaker['expertise'] ?? '') }}" class="form-control form-control-solid" placeholder="Depression, Psychopharmacology"></div>
                                                    <div class="col-md-6"><label class="form-label">Current Position</label><input type="text" name="speakers[{{ $index }}][current_position]" value="{{ $speaker['current_position'] ?? '' }}" class="form-control form-control-solid" placeholder="Professor Emeritus of Psychiatry"></div>
                                                    <div class="col-md-3"><label class="form-label">Country</label><input type="text" name="speakers[{{ $index }}][country]" value="{{ $speaker['country'] ?? '' }}" class="form-control form-control-solid" placeholder="Austria"></div>
                                                    <div class="col-md-3"><label class="form-label">City</label><input type="text" name="speakers[{{ $index }}][city]" value="{{ $speaker['city'] ?? '' }}" class="form-control form-control-solid" placeholder="Vienna"></div>
                                                    <div class="col-md-6"><label class="form-label">Session</label><input type="text" name="speakers[{{ $index }}][session_title]" value="{{ $speaker['session_title'] ?? $module->title }}" class="form-control form-control-solid" placeholder="Session title"></div>
                                                    <div class="col-md-6"><label class="form-label">Speaker Resource URL</label><input type="url" name="speakers[{{ $index }}][profile_resource_url]" value="{{ $speaker['profile_resource_url'] ?? '' }}" class="form-control form-control-solid" placeholder="Profile PDF or reference URL"></div>
                                                    <div class="col-md-8"><label class="form-label">Biography</label><textarea name="speakers[{{ $index }}][bio]" rows="4" class="form-control form-control-solid" placeholder="Speaker profile and experience">{{ $speaker['bio'] ?? '' }}</textarea></div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">Speaker Image</label>
                                                        @if(!empty($speaker['image']))<div class="mb-2"><img src="{{ str_starts_with($speaker['image'], 'web/') ? asset($speaker['image']) : asset('storage/' . $speaker['image']) }}" alt="{{ $speaker['name'] ?? 'Speaker' }}" class="rounded" style="width:90px;height:90px;object-fit:cover"></div>@endif
                                                        <input type="hidden" name="speakers[{{ $index }}][image]" value="{{ $speaker['image'] ?? '' }}">
                                                        <input type="file" name="speaker_images[{{ $index }}]" class="form-control form-control-solid" accept=".jpg,.jpeg,.png,.webp">
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <button type="button" class="btn btn-light-primary btn-sm" id="addSpeakerRow">Add Speaker</button>
                                    <div class="form-text">Each speaker is managed inside this chapter and appears automatically on the participant Speaker tab.</div>
                                </div>
                            </div>

                            <div class="row mb-6">
                                <label class="col-lg-3 col-form-label fw-bold fs-6"><span class="required">Duration / CME Credit</span></label>
                                <div class="col-lg-4">
                                    <input type="text" name="duration" value="{{ old('duration', $module->duration) }}" class="form-control form-control-lg form-control-solid" placeholder="45 mins"/>
                                </div>
                                <div class="col-lg-4">
                                    <input type="number" step="0.1" min="0" name="cme_credit" value="{{ old('cme_credit', $module->cme_credit ?? 1) }}" class="form-control form-control-lg form-control-solid" placeholder="CME credit"/>
                                </div>
                            </div>

                            <div class="row mb-6">
                                <label class="col-lg-3 col-form-label fw-bold fs-6"><span class="required">Thumbnail</span></label>
                                <div class="col-lg-9">
                                    @if($module->thumbnail)
                                        <div class="mb-3">
                                            <img src="{{ asset('storage/' . $module->thumbnail) }}" alt="Thumbnail" class="rounded" style="max-width: 220px;">
                                        </div>
                                        <label class="form-check form-check-sm form-check-custom form-check-solid mb-3">
                                            <input class="form-check-input" type="checkbox" name="thumbnail_remove" value="1">
                                            <span class="form-check-label">Remove current thumbnail</span>
                                        </label>
                                    @endif
                                    <input type="file" name="thumbnail" class="form-control form-control-lg form-control-solid" accept=".jpg,.jpeg,.png,.webp"/>
                                </div>
                            </div>

                            @php
                                $brandRows = old('brands', $module->brands ?: [['label' => '', 'logo' => '']]);
                            @endphp
                            <div class="row mb-6">
                                <label class="col-lg-3 col-form-label fw-bold fs-6">Brand Logos</label>
                                <div class="col-lg-9">
                                    <div id="brandRows">
                                        @foreach($brandRows as $index => $brand)
                                            <div class="row g-3 mb-4 brand-row border rounded p-4">
                                                <div class="col-md-4">
                                                    <label class="form-label">Brand Label</label>
                                                    <input type="text" name="brands[{{ $index }}][label]" value="{{ $brand['label'] ?? '' }}" class="form-control form-control-solid" placeholder="Brand label">
                                                </div>
                                                <div class="col-md-5">
                                                    <label class="form-label">Logo</label>
                                                    <div class="module-brand-preview mb-2 {{ empty($brand['logo']) ? 'd-none' : '' }}">
                                                        @if(!empty($brand['logo']))
                                                            <img src="{{ asset('storage/' . $brand['logo']) }}" alt="{{ $brand['label'] ?? 'Brand logo' }}">
                                                        @endif
                                                    </div>
                                                    <input type="hidden" name="brands[{{ $index }}][logo]" value="{{ $brand['logo'] ?? '' }}">
                                                    <input type="hidden" name="brand_crops[{{ $index }}][x]" class="brand-crop-x">
                                                    <input type="hidden" name="brand_crops[{{ $index }}][y]" class="brand-crop-y">
                                                    <input type="hidden" name="brand_crops[{{ $index }}][width]" class="brand-crop-width">
                                                    <input type="hidden" name="brand_crops[{{ $index }}][height]" class="brand-crop-height">
                                                    <input type="file" name="brand_logos[{{ $index }}]" class="form-control form-control-solid brand-logo-input" accept=".jpg,.jpeg,.png,.webp">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">Fixed Size</label>
                                                    <div class="form-control form-control-solid text-muted">260 x 80</div>
                                                </div>
                                                <div class="col-md-1 d-flex align-items-end">
                                                    <button type="button" class="btn btn-light-danger remove-brand-row w-100">X</button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <button type="button" class="btn btn-light-primary btn-sm" id="addBrandRow">Add Brand</button>
                                    <div class="form-text">Logo preview is fixed at 260 x 80 and cropped/fitted inside the box.</div>
                                </div>
                            </div>

                            <div class="row mb-6">
                                <label class="col-lg-3 col-form-label fw-bold fs-6">Full Session Video</label>
                                <div class="col-lg-9">
                                    <input type="url" name="video_url" value="{{ old('video_url', $module->video_url) }}" class="form-control form-control-lg form-control-solid" placeholder="https://www.youtube.com/watch?v=..."/>
                                </div>
                            </div>

                            <div class="row mb-6">
                                <label class="col-lg-3 col-form-label fw-bold fs-6">Live Webinar Join URL</label>
                                <div class="col-lg-9"><input type="url" name="join_url" value="{{ old('join_url', $module->join_url) }}" class="form-control form-control-lg form-control-solid" placeholder="https://meeting-provider.example/session"></div>
                            </div>

                            <div class="row mb-6">
                                <label class="col-lg-3 col-form-label fw-bold fs-6"><span class="required">Overview</span></label>
                                <div class="col-lg-9">
                                    <textarea name="overview" rows="4" class="form-control form-control-lg form-control-solid" placeholder="Chapter overview">{{ old('overview', $module->overview) }}</textarea>
                                </div>
                            </div>

                            <div class="row mb-6">
                                <label class="col-lg-3 col-form-label fw-bold fs-6">Key Takeaways</label>
                                <div class="col-lg-9">
                                    <textarea name="key_takeaways" rows="4" class="form-control form-control-lg form-control-solid" placeholder="One takeaway per line">{{ old('key_takeaways', $module->key_takeaways) }}</textarea>
                                </div>
                            </div>

                            <div class="row mb-6">
                                <label class="col-lg-3 col-form-label fw-bold fs-6">Chapter Resources</label>
                                <div class="col-lg-9">
                                    @if($module->resourceDocuments->isNotEmpty())
                                        <div class="mb-4">
                                            @foreach($module->resourceDocuments as $resource)
                                                <div class="bg-light rounded p-3 mb-3">
                                                    <div class="row g-3 align-items-end">
                                                        <div class="col-md-5">
                                                            <label class="form-label">Resource Label</label>
                                                            <input type="text" name="existing_resource_labels[{{ $resource->id }}]" value="{{ old('existing_resource_labels.' . $resource->id, $resource->label) }}" class="form-control form-control-lg form-control-solid">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label">Category</label>
                                                            <select name="existing_resource_categories[{{ $resource->id }}]" class="form-select form-select-lg form-select-solid">
                                                                @foreach(['full_recording' => 'Full Webinar Recording', 'post_read' => 'Post-read / PDF', 'snippet_video' => 'Snippet Video', 'case_book' => 'Case Booklet', 'general' => 'General Resource'] as $value => $label)
                                                                    <option value="{{ $value }}" @selected(old('existing_resource_categories.' . $resource->id, $resource->category ?: 'general') === $value)>{{ $label }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3"><label class="form-label">Type</label><select name="existing_resource_types[{{ $resource->id }}]" class="form-select form-select-lg form-select-solid">@foreach(['auto'=>'Auto detect','pdf'=>'PDF','video'=>'Video','url'=>'External link'] as $value=>$label)<option value="{{ $value }}" @selected(old('existing_resource_types.' . $resource->id, $resource->resource_type ?: 'auto') === $value)>{{ $label }}</option>@endforeach</select></div>
                                                        <div class="col-md-3">
                                                            <label class="form-check form-check-sm form-check-custom form-check-solid mb-3">
                                                                <input class="form-check-input" type="checkbox" name="remove_resources[]" value="{{ $resource->id }}">
                                                                <span class="form-check-label">Remove</span>
                                                            </label>
                                                        </div>
                                                        <div class="col-12"><label class="form-label">Description</label><textarea name="existing_resource_descriptions[{{ $resource->id }}]" rows="2" class="form-control form-control-solid" placeholder="Short resource description">{{ old('existing_resource_descriptions.' . $resource->id, $resource->description) }}</textarea></div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Upload / Replace File or Video</label>
                                                            <input type="file" name="existing_resource_files[{{ $resource->id }}]" class="form-control form-control-lg form-control-solid" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp,.mp4,.webm,.mov">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Or External / Embed URL</label>
                                                            <input type="url" name="existing_resource_urls[{{ $resource->id }}]" value="{{ old('existing_resource_urls.' . $resource->id, $resource->external_url) }}" class="form-control form-control-lg form-control-solid" placeholder="https://youtube.com/watch?v=...">
                                                        </div>
                                                        <div class="col-md-4"><label class="form-label">Available From</label><input type="text" name="existing_resource_available_at[{{ $resource->id }}]" value="{{ old('existing_resource_available_at.' . $resource->id, $resource->getRawOriginal('available_at') ? \Carbon\CarbonImmutable::parse($resource->getRawOriginal('available_at'), 'UTC')->setTimezone('Asia/Kolkata')->format('Y-m-d H:i') : '') }}" class="form-control form-control-solid module-datetime" placeholder="Optional"></div>
                                                        <div class="col-md-4"><label class="form-label">Status</label><select name="existing_resource_statuses[{{ $resource->id }}]" class="form-select form-select-solid"><option value="active" @selected($resource->status === 'active')>Active</option><option value="inactive" @selected($resource->status === 'inactive')>Inactive</option></select></div>
                                                        <div class="col-md-4"><label class="form-label">Thumbnail</label>@if($resource->thumbnail)<img src="{{ asset('storage/' . $resource->thumbnail) }}" alt="" class="d-block rounded mb-2" style="width:100px;height:60px;object-fit:cover">@endif<input type="file" name="existing_resource_thumbnails[{{ $resource->id }}]" class="form-control form-control-solid" accept=".jpg,.jpeg,.png,.webp"></div>
                                                        @if($resource->file || $resource->external_url)
                                                            <div class="col-12"><a href="{{ $resource->external_url ?: asset('storage/' . $resource->file) }}" target="_blank" class="btn btn-light-primary btn-sm">Open current {{ strtoupper($resource->file_type ?: 'resource') }}</a></div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div id="resourceRows">
                                        <div class="row g-3 mb-4 resource-row border rounded p-4">
                                            <div class="col-md-5">
                                                <label class="form-label">Resource Label</label>
                                                <input type="text" name="resource_labels[0]" class="form-control form-control-lg form-control-solid" placeholder="Resource label">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Category</label>
                                                <select name="resource_categories[0]" class="form-select form-select-lg form-select-solid"><option value="full_recording">Full Webinar Recording</option><option value="post_read">Post-read / PDF</option><option value="snippet_video">Snippet Video</option><option value="case_book">Case Booklet</option><option value="general" selected>General Resource</option></select>
                                            </div>
                                            <div class="col-md-3"><label class="form-label">Type</label><select name="resource_types[0]" class="form-select form-select-lg form-select-solid"><option value="auto">Auto detect</option><option value="pdf">PDF</option><option value="video">Video</option><option value="url">External link</option></select></div>
                                            <div class="col-md-2"><label class="form-label">Status</label><select name="resource_statuses[0]" class="form-select form-select-lg form-select-solid"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                                            <div class="col-md-1 d-flex align-items-end">
                                                <button type="button" class="btn btn-sm btn-light-danger remove-resource-row">X</button>
                                            </div>
                                            <div class="col-12"><label class="form-label">Description</label><textarea name="resource_descriptions[0]" rows="2" class="form-control form-control-solid" placeholder="Short resource description"></textarea></div>
                                            <div class="col-md-6"><label class="form-label">Upload File / Video</label><input type="file" name="resource_files[0]" class="form-control form-control-lg form-control-solid" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp,.mp4,.webm,.mov"></div>
                                            <div class="col-md-6"><label class="form-label">Or External / Embed URL</label><input type="url" name="resource_urls[0]" class="form-control form-control-lg form-control-solid" placeholder="https://youtube.com/watch?v=..."></div>
                                            <div class="col-md-6"><label class="form-label">Available From</label><input type="text" name="resource_available_at[0]" class="form-control form-control-solid module-datetime" placeholder="Optional"></div>
                                            <div class="col-md-6"><label class="form-label">Thumbnail</label><input type="file" name="resource_thumbnails[0]" class="form-control form-control-solid" accept=".jpg,.jpeg,.png,.webp"></div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-light-primary btn-sm" id="addResourceRow">Add Resource</button>
                                    <div class="form-text">For each resource, upload one file/video or enter one external URL. Allowed uploads: PDF, DOC, DOCX, JPG, PNG, WEBP, MP4, WEBM, MOV.</div>
                                </div>
                            </div>

                            <div class="row mb-6">
                                <label class="col-lg-3 col-form-label fw-bold fs-6">Certificate Template</label>
                                <div class="col-lg-9">
                                    @if($module->certificate_template)
                                        <div class="mb-3">
                                            <a href="{{ asset('storage/' . $module->certificate_template) }}" target="_blank" class="btn btn-light-primary">
                                                View Current Certificate PDF
                                            </a>
                                        </div>
                                        <label class="form-check form-check-sm form-check-custom form-check-solid mb-3">
                                            <input class="form-check-input" type="checkbox" name="certificate_template_remove" value="1">
                                            <span class="form-check-label">Remove current certificate template</span>
                                        </label>
                                    @endif
                                    <input
                                        type="file"
                                        name="certificate_template"
                                        id="certificateTemplateInput"
                                        class="form-control form-control-lg form-control-solid"
                                        accept=".pdf"
                                        data-default-pdf-url="{{ route('admin.modules.certificate_template_preview') }}"
                                        data-current-pdf-url="{{ $module->id ? route('admin.modules.certificate_template_preview', $module->id) : route('admin.modules.certificate_template_preview') }}"
                                    >
                                    <div class="form-text">Upload an chapter-specific certificate PDF. If empty, the default certificate template is used.</div>

                                    <div class="row g-3 mt-3">
                                        <div class="col-md-3">
                                            <label class="form-label">Name Area</label>
                                            <select class="form-select form-select-solid" id="certificateNameArea">
                                                <option value="">Custom / Auto</option>
                                                <option value="center">Center</option>
                                                <option value="upper">Upper Center</option>
                                                <option value="lower">Lower Center</option>
                                                <option value="left">Left Area</option>
                                                <option value="right">Right Area</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Name X Position</label>
                                            <input type="number" step="0.01" min="0" name="certificate_name_x" id="certificateNameX" value="{{ old('certificate_name_x', $module->certificate_name_x ?? '') }}" class="form-control form-control-solid" placeholder="Auto center">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Name Y Position</label>
                                            <input type="number" step="0.01" min="0" name="certificate_name_y" id="certificateNameY" value="{{ old('certificate_name_y', $module->certificate_name_y ?? '') }}" class="form-control form-control-solid" placeholder="292">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Font Size</label>
                                            <input type="number" min="8" max="72" name="certificate_font_size" value="{{ old('certificate_font_size', $module->certificate_font_size ?? '') }}" class="form-control form-control-solid" placeholder="Auto">
                                        </div>
                                    </div>
                                    <div class="form-text mt-2">Use Name Area for quick placement, or enter exact PDF points from the bottom-left corner. Leave X blank to auto-center the user name.</div>

                                    <div class="certificate-preview-panel mt-4">
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                                            <div>
                                                <div class="fw-bold text-gray-800">Certificate Preview</div>
                                                <div class="form-text">Click on the PDF where the delegate name should start.</div>
                                            </div>
                                            <button type="button" class="btn btn-light-primary btn-sm" id="refreshCertificatePreview">Refresh Preview</button>
                                        </div>
                                        <div class="certificate-preview-frame" id="certificatePreviewFrame">
                                            <canvas id="certificatePreviewCanvas"></canvas>
                                            <div class="certificate-name-marker" id="certificateNameMarker">Dr. Sample Name</div>
                                            <div class="certificate-preview-state" id="certificatePreviewState">Loading preview...</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-6">
                                <label class="col-lg-3 col-form-label fw-bold fs-6"><span class="required">Chapter Schedule</span></label>
                                <div class="col-lg-3">
                                    <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $module->sort_order ?? 0) }}" class="form-control form-control-lg form-control-solid" placeholder="Sort order"/>
                                </div>
                                <div class="col-lg-3">
                                    <input type="text" name="start_at" value="{{ old('start_at', optional($module->start_at)->format('Y-m-d H:i')) }}" class="form-control form-control-lg form-control-solid module-datetime" placeholder="Start date time"/>
                                </div>
                                <div class="col-lg-3">
                                    <input type="text" name="end_at" value="{{ old('end_at', optional($module->end_at)->format('Y-m-d H:i')) }}" class="form-control form-control-lg form-control-solid module-datetime" placeholder="End date time"/>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer d-flex justify-content-end py-6 px-9">
                            <a href="{{ route('admin.modules') }}" class="btn btn-light btn-active-light-primary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary" id="kt_submit">
                                <span class="indicator-label">Save</span>
                                <span class="indicator-progress">Please wait...
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="brandCropModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Crop Brand Logo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="brand-crop-area">
                        <img id="brandCropImage" src="" alt="Crop brand logo">
                    </div>
                    <div class="form-text mt-3">Crop box follows the uploaded logo shape. Backend saves the cropped logo in a fixed 260 x 80 canvas.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="applyBrandCrop">Apply Crop</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        "use strict";
        KTUtil.onDOMContentLoaded(function () {
            const form = document.querySelector('#kt_module_form');
            const submitBtn = document.querySelector('#kt_submit');
            let brandCropper = null;
            let activeBrandInput = null;
            const brandCropModalEl = document.getElementById('brandCropModal');
            const brandCropModal = new bootstrap.Modal(brandCropModalEl);
            const brandCropImage = document.getElementById('brandCropImage');
            const brandLogoWidth = 260;
            const brandLogoHeight = 80;
            const certificateTemplateInput = document.getElementById('certificateTemplateInput');
            const certificatePreviewFrame = document.getElementById('certificatePreviewFrame');
            const certificatePreviewCanvas = document.getElementById('certificatePreviewCanvas');
            const certificatePreviewState = document.getElementById('certificatePreviewState');
            const certificateNameMarker = document.getElementById('certificateNameMarker');
            const certificateNameX = document.getElementById('certificateNameX');
            const certificateNameY = document.getElementById('certificateNameY');
            const certificateFontSize = document.querySelector('[name="certificate_font_size"]');
            const certificateRemoveInput = document.querySelector('[name="certificate_template_remove"]');
            let certificatePdfUrl = certificateTemplateInput?.dataset.currentPdfUrl || '';
            let certificateObjectUrl = null;
            let certificatePageSize = {width: 841.89, height: 595.276};
            let certificateViewport = null;
            let isDraggingCertificateName = false;

            const validator = FormValidation.formValidation(form, {
                fields: {
                    title: {
                        validators: {
                            notEmpty: {message: "Title is required"}
                        }
                    },
                    course_id: {
                        validators: {
                            notEmpty: {message: "Programme is required"}
                        }
                    },
                    duration: {
                        validators: {
                            notEmpty: {message: "Duration is required"}
                        }
                    },
                    cme_credit: {
                        validators: {
                            notEmpty: {message: "CME credit is required"}
                        }
                    },
                    video_url: {
                        validators: {
                            uri: {message: "Please enter a valid YouTube link"}
                        }
                    },
                    overview: {
                        validators: {
                            notEmpty: {message: "Overview is required"}
                        }
                    },
                    sort_order: {
                        validators: {
                            notEmpty: {message: "Sort order is required"}
                        }
                    },
                    start_at: {
                        validators: {
                            notEmpty: {message: "Start date time is required"}
                        }
                    },
                    end_at: {
                        validators: {
                            notEmpty: {message: "End date time is required"}
                        }
                    },
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap: new FormValidation.plugins.Bootstrap5({rowSelector: '.row'})
                }
            });

            submitBtn.addEventListener('click', function (e) {
                e.preventDefault();
                validator.validate().then(function (status) {
                    if (status === 'Valid') {
                        submitBtn.setAttribute('data-kt-indicator', 'on');
                        submitBtn.disabled = true;
                        form.submit();
                    }
                });
            });

            $('.module-datetime').flatpickr({
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                altInput: true,
                altFormat: "d M Y, h:i K",
            });

            function certificateSampleTextWidth(fontSize) {
                return certificateNameMarker.textContent.length * fontSize * 0.56;
            }

            function certificateDisplayPosition() {
                const fontSize = parseInt(certificateFontSize.value || '30', 10);
                const typedX = certificateNameX.value;
                const typedY = certificateNameY.value;
                const x = typedX === ''
                    ? Math.max(120, (certificatePageSize.width - certificateSampleTextWidth(fontSize)) / 2)
                    : parseFloat(typedX);
                const defaultY = certificatePageSize.height * 0.49;
                const y = typedY === '' ? defaultY : parseFloat(typedY);

                return {
                    x: Number.isFinite(x) ? x : 120,
                    y: Number.isFinite(y) ? y : defaultY,
                    fontSize: Number.isFinite(fontSize) ? fontSize : 30,
                };
            }

            function updateCertificateMarker() {
                if (!certificatePreviewFrame || !certificateNameMarker) {
                    return;
                }

                const canvasRect = certificatePreviewCanvas.getBoundingClientRect();
                if (!canvasRect.width || !canvasRect.height) {
                    return;
                }

                if (!certificateViewport) {
                    return;
                }

                const position = certificateDisplayPosition();
                const [viewportX, viewportY] = certificateViewport.convertToViewportPoint(position.x, position.y);
                const renderedScale = canvasRect.width / certificateViewport.width;
                const pdfScale = Math.hypot(certificateViewport.transform[0], certificateViewport.transform[1]);
                const baselineAngle = Math.atan2(
                    certificateViewport.transform[1],
                    certificateViewport.transform[0]
                ) * (180 / Math.PI);

                certificateNameMarker.style.left = `${certificatePreviewCanvas.offsetLeft + (viewportX * renderedScale)}px`;
                certificateNameMarker.style.top = `${certificatePreviewCanvas.offsetTop + (viewportY * renderedScale)}px`;
                certificateNameMarker.style.fontSize = `${Math.max(10, position.fontSize * pdfScale * renderedScale)}px`;
                certificateNameMarker.style.transform = `translateY(-100%) rotate(${baselineAngle}deg)`;
                certificateNameMarker.classList.remove('d-none');
            }

            function setCertificatePositionFromPointer(event) {
                const rect = certificatePreviewCanvas.getBoundingClientRect();
                if (!rect.width || !rect.height) {
                    return;
                }

                if (!certificateViewport) {
                    return;
                }

                const clickX = Math.min(Math.max(event.clientX - rect.left, 0), rect.width);
                const clickY = Math.min(Math.max(event.clientY - rect.top, 0), rect.height);
                const renderedScaleX = rect.width / certificateViewport.width;
                const renderedScaleY = rect.height / certificateViewport.height;
                const [pdfX, pdfY] = certificateViewport.convertToPdfPoint(
                    clickX / renderedScaleX,
                    clickY / renderedScaleY
                );
                certificateNameX.value = pdfX.toFixed(2);
                certificateNameY.value = pdfY.toFixed(2);
                $('#certificateNameArea').val('');
                updateCertificateMarker();
            }

            async function renderCertificatePreview(url) {
                if (!certificatePreviewCanvas || !certificatePreviewFrame) {
                    return;
                }

                if (!window.pdfjsLib) {
                    certificatePreviewState.textContent = 'PDF preview library could not load.';
                    certificatePreviewState.classList.remove('d-none');
                    return;
                }

                pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
                certificatePreviewState.textContent = 'Loading preview...';
                certificatePreviewState.classList.remove('d-none');
                certificateNameMarker.classList.add('d-none');

                try {
                    const pdf = await pdfjsLib.getDocument(url).promise;
                    const page = await pdf.getPage(1);
                    const baseViewport = page.getViewport({scale: 1});
                    certificatePageSize = {
                        width: baseViewport.width,
                        height: baseViewport.height,
                    };

                    const frameWidth = Math.max(280, certificatePreviewFrame.clientWidth - 28);
                    const scale = Math.min(1.4, frameWidth / baseViewport.width);
                    const viewport = page.getViewport({scale});
                    certificateViewport = viewport;
                    const context = certificatePreviewCanvas.getContext('2d');
                    const outputScale = window.devicePixelRatio || 1;

                    certificatePreviewCanvas.width = Math.floor(viewport.width * outputScale);
                    certificatePreviewCanvas.height = Math.floor(viewport.height * outputScale);
                    certificatePreviewCanvas.style.width = `${viewport.width}px`;
                    certificatePreviewCanvas.style.height = `${viewport.height}px`;
                    context.setTransform(outputScale, 0, 0, outputScale, 0, 0);
                    context.clearRect(0, 0, viewport.width, viewport.height);

                    await page.render({canvasContext: context, viewport}).promise;
                    certificatePreviewState.classList.add('d-none');
                    updateCertificateMarker();
                } catch (error) {
                    certificatePreviewState.textContent = 'Unable to render this PDF preview.';
                    certificatePreviewState.classList.remove('d-none');
                }
            }

            function setCertificatePdfUrlFromInput() {
                const file = certificateTemplateInput?.files?.[0];
                if (certificateObjectUrl) {
                    URL.revokeObjectURL(certificateObjectUrl);
                    certificateObjectUrl = null;
                }

                if (file) {
                    certificateObjectUrl = URL.createObjectURL(file);
                    certificatePdfUrl = certificateObjectUrl;
                    return;
                }

                certificatePdfUrl = certificateRemoveInput?.checked
                    ? certificateTemplateInput.dataset.defaultPdfUrl
                    : certificateTemplateInput.dataset.currentPdfUrl;
            }

            function refreshCertificatePreview() {
                setCertificatePdfUrlFromInput();
                if (certificatePdfUrl) {
                    renderCertificatePreview(certificatePdfUrl);
                }
            }

            certificateTemplateInput?.addEventListener('change', refreshCertificatePreview);
            certificateRemoveInput?.addEventListener('change', refreshCertificatePreview);
            document.getElementById('refreshCertificatePreview')?.addEventListener('click', refreshCertificatePreview);
            certificateNameX?.addEventListener('input', updateCertificateMarker);
            certificateNameY?.addEventListener('input', updateCertificateMarker);
            certificateFontSize?.addEventListener('input', updateCertificateMarker);
            window.addEventListener('resize', updateCertificateMarker);

            certificatePreviewFrame?.addEventListener('pointerdown', function (event) {
                if (event.button !== 0) {
                    return;
                }

                const rect = certificatePreviewCanvas.getBoundingClientRect();
                const isInsideCanvas = event.clientX >= rect.left
                    && event.clientX <= rect.right
                    && event.clientY >= rect.top
                    && event.clientY <= rect.bottom;
                if (!isInsideCanvas) {
                    return;
                }

                event.preventDefault();
                isDraggingCertificateName = true;
                certificatePreviewFrame.classList.add('is-positioning');
                certificatePreviewFrame.setPointerCapture(event.pointerId);
                setCertificatePositionFromPointer(event);
            });

            certificatePreviewFrame?.addEventListener('pointermove', function (event) {
                if (!isDraggingCertificateName) {
                    return;
                }

                event.preventDefault();
                setCertificatePositionFromPointer(event);
            });

            certificatePreviewFrame?.addEventListener('pointerup', function (event) {
                if (!isDraggingCertificateName) {
                    return;
                }

                isDraggingCertificateName = false;
                certificatePreviewFrame.classList.remove('is-positioning');
                certificatePreviewFrame.releasePointerCapture(event.pointerId);
            });

            certificatePreviewFrame?.addEventListener('pointercancel', function (event) {
                isDraggingCertificateName = false;
                certificatePreviewFrame.classList.remove('is-positioning');
                if (certificatePreviewFrame.hasPointerCapture(event.pointerId)) {
                    certificatePreviewFrame.releasePointerCapture(event.pointerId);
                }
            });

            $('#certificateNameArea').on('change', function () {
                const presets = {
                    center: {x: '', y: certificatePageSize.height * 0.49},
                    upper: {x: '', y: certificatePageSize.height * 0.6},
                    lower: {x: '', y: certificatePageSize.height * 0.39},
                    left: {x: certificatePageSize.width * 0.21, y: certificatePageSize.height * 0.49},
                    right: {x: certificatePageSize.width * 0.62, y: certificatePageSize.height * 0.49},
                };
                const preset = presets[this.value];
                if (!preset) {
                    return;
                }

                $('#certificateNameX').val(preset.x === '' ? '' : preset.x.toFixed(2));
                $('#certificateNameY').val(preset.y.toFixed(2));
                updateCertificateMarker();
            });

            refreshCertificatePreview();

            let speakerIndex = document.querySelectorAll('#speakerRows .speaker-row').length;
            $('#addSpeakerRow').on('click', function () {
                const index = speakerIndex++;
                $('#speakerRows').append(`
                    <div class="speaker-row border rounded p-4 mb-4" data-speaker-index="${index}">
                        <div class="row g-3">
                            <div class="col-md-5"><label class="form-label">Speaker Name</label><input type="text" name="speakers[${index}][name]" class="form-control form-control-solid" placeholder="Prof. Speaker Name" required></div>
                            <div class="col-md-3"><label class="form-label">Role</label><input type="text" name="speakers[${index}][role]" value="Speaker" class="form-control form-control-solid" placeholder="Speaker"></div>
                            <div class="col-md-3"><label class="form-label">Designation</label><input type="text" name="speakers[${index}][designation]" class="form-control form-control-solid" placeholder="Professor of Psychiatry" required></div>
                            <div class="col-md-1 d-flex align-items-end"><button type="button" class="btn btn-light-danger remove-speaker-row w-100">X</button></div>
                            <div class="col-md-6"><label class="form-label">Institution / Hospital</label><input type="text" name="speakers[${index}][hospital]" class="form-control form-control-solid" placeholder="Medical University / Hospital"></div>
                            <div class="col-md-6"><label class="form-label">Areas of Expertise</label><input type="text" name="speakers[${index}][expertise]" class="form-control form-control-solid" placeholder="Depression, Psychopharmacology"></div>
                            <div class="col-md-6"><label class="form-label">Current Position</label><input type="text" name="speakers[${index}][current_position]" class="form-control form-control-solid" placeholder="Professor Emeritus of Psychiatry"></div>
                            <div class="col-md-3"><label class="form-label">Country</label><input type="text" name="speakers[${index}][country]" class="form-control form-control-solid"></div>
                            <div class="col-md-3"><label class="form-label">City</label><input type="text" name="speakers[${index}][city]" class="form-control form-control-solid"></div>
                            <div class="col-md-6"><label class="form-label">Session</label><input type="text" name="speakers[${index}][session_title]" class="form-control form-control-solid"></div>
                            <div class="col-md-6"><label class="form-label">Speaker Resource URL</label><input type="url" name="speakers[${index}][profile_resource_url]" class="form-control form-control-solid" placeholder="Profile PDF or reference URL"></div>
                            <div class="col-md-8"><label class="form-label">Biography</label><textarea name="speakers[${index}][bio]" rows="4" class="form-control form-control-solid" placeholder="Speaker profile and experience"></textarea></div>
                            <div class="col-md-4"><label class="form-label">Speaker Image</label><input type="hidden" name="speakers[${index}][image]" value=""><input type="file" name="speaker_images[${index}]" class="form-control form-control-solid" accept=".jpg,.jpeg,.png,.webp"></div>
                        </div>
                    </div>
                `);
            });

            $(document).on('click', '.remove-speaker-row', function () {
                if ($('#speakerRows .speaker-row').length > 1) {
                    $(this).closest('.speaker-row').remove();
                } else {
                    $(this).closest('.speaker-row').find('input:not([type="hidden"]), textarea').val('');
                    $(this).closest('.speaker-row').find('input[type="hidden"]').val('');
                }
            });

            $('#addResourceRow').on('click', function () {
                const index = $('#resourceRows .resource-row').length;
                $('#resourceRows').append(`
                    <div class="row g-3 mb-4 resource-row border rounded p-4">
                        <div class="col-md-5">
                            <label class="form-label">Resource Label</label>
                            <input type="text" name="resource_labels[${index}]" class="form-control form-control-lg form-control-solid" placeholder="Resource label">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <select name="resource_categories[${index}]" class="form-select form-select-lg form-select-solid"><option value="full_recording">Full Webinar Recording</option><option value="post_read">Post-read / PDF</option><option value="snippet_video">Snippet Video</option><option value="case_book">Case Booklet</option><option value="general" selected>General Resource</option></select>
                        </div>
                        <div class="col-md-3"><label class="form-label">Type</label><select name="resource_types[${index}]" class="form-select form-select-lg form-select-solid"><option value="auto">Auto detect</option><option value="pdf">PDF</option><option value="video">Video</option><option value="url">External link</option></select></div>
                        <div class="col-md-2"><label class="form-label">Status</label><select name="resource_statuses[${index}]" class="form-select form-select-lg form-select-solid"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="button" class="btn btn-sm btn-light-danger remove-resource-row">X</button>
                        </div>
                        <div class="col-12"><label class="form-label">Description</label><textarea name="resource_descriptions[${index}]" rows="2" class="form-control form-control-solid" placeholder="Short resource description"></textarea></div>
                        <div class="col-md-6"><label class="form-label">Upload File / Video</label><input type="file" name="resource_files[${index}]" class="form-control form-control-lg form-control-solid" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp,.mp4,.webm,.mov"></div>
                        <div class="col-md-6"><label class="form-label">Or External / Embed URL</label><input type="url" name="resource_urls[${index}]" class="form-control form-control-lg form-control-solid" placeholder="https://youtube.com/watch?v=..."></div>
                        <div class="col-md-6"><label class="form-label">Available From</label><input type="text" name="resource_available_at[${index}]" class="form-control form-control-solid module-datetime" placeholder="Optional"></div>
                        <div class="col-md-6"><label class="form-label">Thumbnail</label><input type="file" name="resource_thumbnails[${index}]" class="form-control form-control-solid" accept=".jpg,.jpeg,.png,.webp"></div>
                    </div>
                `);
                $('.module-datetime').flatpickr({enableTime: true, dateFormat: "Y-m-d H:i", altInput: true, altFormat: "d M Y, h:i K"});
            });

            function refreshResourceIndexes() {
                $('#resourceRows .resource-row').each(function (index) {
                    $(this).find('input, select, textarea').each(function () {
                        this.name = this.name.replace(/resource_(labels|categories|descriptions|types|available_at|statuses|files|urls|thumbnails)\[\d+]/, `resource_$1[${index}]`);
                    });
                });
            }

            $(document).on('click', '.remove-resource-row', function () {
                if ($('.resource-row').length > 1) {
                    $(this).closest('.resource-row').remove();
                    refreshResourceIndexes();
                } else {
                    const row = $(this).closest('.resource-row');
                    row.find('input').val('');
                    row.find('select').val('general');
                }
            });

            function refreshBrandIndexes() {
                $('#brandRows .brand-row').each(function (index) {
                    $(this).find('input').each(function () {
                        this.name = this.name.replace(/brands\[\d+]/, `brands[${index}]`);
                        this.name = this.name.replace(/brand_crops\[\d+]/, `brand_crops[${index}]`);
                        this.name = this.name.replace(/brand_logos\[\d+]/, `brand_logos[${index}]`);
                    });
                });
            }

            $('#addBrandRow').on('click', function () {
                const index = $('#brandRows .brand-row').length;
                $('#brandRows').append(`
                    <div class="row g-3 mb-4 brand-row border rounded p-4">
                        <div class="col-md-4">
                            <label class="form-label">Brand Label</label>
                            <input type="text" name="brands[${index}][label]" class="form-control form-control-solid" placeholder="Brand label">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Logo</label>
                            <div class="module-brand-preview mb-2 d-none"></div>
                            <input type="hidden" name="brands[${index}][logo]" value="">
                            <input type="hidden" name="brand_crops[${index}][x]" class="brand-crop-x">
                            <input type="hidden" name="brand_crops[${index}][y]" class="brand-crop-y">
                            <input type="hidden" name="brand_crops[${index}][width]" class="brand-crop-width">
                            <input type="hidden" name="brand_crops[${index}][height]" class="brand-crop-height">
                            <input type="file" name="brand_logos[${index}]" class="form-control form-control-solid brand-logo-input" accept=".jpg,.jpeg,.png,.webp">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Fixed Size</label>
                            <div class="form-control form-control-solid text-muted">260 x 80</div>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="button" class="btn btn-light-danger remove-brand-row w-100">X</button>
                        </div>
                    </div>
                `);
            });

            $(document).on('click', '.remove-brand-row', function () {
                if ($('.brand-row').length > 1) {
                    $(this).closest('.brand-row').remove();
                    refreshBrandIndexes();
                } else {
                    const row = $(this).closest('.brand-row');
                    row.find('input').val('');
                    row.find('.module-brand-preview').addClass('d-none').empty();
                }
            });

            $(document).on('change', '.brand-logo-input', function () {
                const file = this.files?.[0];
                const preview = $(this).closest('.brand-row').find('.module-brand-preview');
                if (!file) {
                    preview.addClass('d-none').empty();
                    return;
                }

                const reader = new FileReader();
                reader.onload = event => {
                    activeBrandInput = this;
                    brandCropImage.src = event.target.result;
                    brandCropModal.show();
                };
                reader.readAsDataURL(file);
            });

            brandCropModalEl.addEventListener('shown.bs.modal', function () {
                if (brandCropper) {
                    brandCropper.destroy();
                }

                brandCropper = new Cropper(brandCropImage, {
                    aspectRatio: NaN,
                    viewMode: 1,
                    dragMode: 'move',
                    autoCropArea: 0.9,
                    cropBoxMovable: false,
                    cropBoxResizable: false,
                    toggleDragModeOnDblclick: false,
                    zoomOnWheel: true,
                    background: false,
                    responsive: true,
                    ready() {
                        const containerData = brandCropper.getContainerData();
                        const imageData = brandCropper.getImageData();
                        const logoRatio = Math.min(5.2, Math.max(2.4, imageData.naturalWidth / imageData.naturalHeight));
                        let cropBoxWidth = Math.min(380, containerData.width - 40);
                        let cropBoxHeight = cropBoxWidth / logoRatio;

                        if (cropBoxHeight > containerData.height - 40) {
                            cropBoxHeight = containerData.height - 40;
                            cropBoxWidth = cropBoxHeight * logoRatio;
                        }

                        brandCropper.setAspectRatio(logoRatio);

                        brandCropper.setCropBoxData({
                            width: cropBoxWidth,
                            height: cropBoxHeight,
                            left: (containerData.width - cropBoxWidth) / 2,
                            top: (containerData.height - cropBoxHeight) / 2,
                        });
                    },
                });
            });

            brandCropModalEl.addEventListener('hidden.bs.modal', function () {
                if (brandCropper) {
                    brandCropper.destroy();
                    brandCropper = null;
                }
            });

            $('#applyBrandCrop').on('click', function () {
                if (!brandCropper || !activeBrandInput) {
                    return;
                }

                const cropData = brandCropper.getData(true);
                const row = $(activeBrandInput).closest('.brand-row');
                row.find('.brand-crop-x').val(cropData.x);
                row.find('.brand-crop-y').val(cropData.y);
                row.find('.brand-crop-width').val(cropData.width);
                row.find('.brand-crop-height').val(cropData.height);

                const croppedCanvas = brandCropper.getCroppedCanvas({
                    imageSmoothingQuality: 'high',
                });
                const previewCanvas = document.createElement('canvas');
                const context = previewCanvas.getContext('2d');
                previewCanvas.width = brandLogoWidth;
                previewCanvas.height = brandLogoHeight;
                context.fillStyle = '#ffffff';
                context.fillRect(0, 0, brandLogoWidth, brandLogoHeight);

                const scale = Math.min(brandLogoWidth / croppedCanvas.width, brandLogoHeight / croppedCanvas.height);
                const drawWidth = croppedCanvas.width * scale;
                const drawHeight = croppedCanvas.height * scale;
                context.drawImage(
                    croppedCanvas,
                    (brandLogoWidth - drawWidth) / 2,
                    (brandLogoHeight - drawHeight) / 2,
                    drawWidth,
                    drawHeight
                );

                row.find('.module-brand-preview')
                    .removeClass('d-none')
                    .html(`<img src="${previewCanvas.toDataURL('image/jpeg', 0.9)}" alt="Brand preview">`);

                brandCropModal.hide();
            });
        });
    </script>
@endpush

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
    <style>
        .module-brand-preview {
            width: 260px;
            height: 80px;
            border: 1px solid #e4e6ef;
            border-radius: 8px;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding: 8px;
        }

        .module-brand-preview img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .brand-crop-area {
            width: 100%;
            height: 420px;
            background: #f5f8fa;
            border-radius: 8px;
            overflow: hidden;
        }

        .brand-crop-area img {
            display: block;
            max-width: 100%;
        }

        .certificate-preview-panel {
            border: 1px solid #e4e6ef;
            border-radius: 8px;
            padding: 16px;
            background: #f9fafb;
        }

        .certificate-preview-frame {
            position: relative;
            min-height: 260px;
            max-height: 620px;
            overflow: auto;
            border: 1px solid #d8dce6;
            border-radius: 8px;
            background: #eef1f5;
            padding: 14px;
            cursor: crosshair;
            touch-action: none;
            user-select: none;
        }

        .certificate-preview-frame.is-positioning {
            cursor: grabbing;
        }

        .certificate-preview-frame canvas {
            display: block;
            max-width: 100%;
            background: #ffffff;
            box-shadow: 0 8px 20px rgba(16, 24, 40, 0.12);
        }

        .certificate-name-marker {
            position: absolute;
            transform: translateY(-100%);
            transform-origin: left bottom;
            padding: 2px 0;
            border: 1px dashed #0f766e;
            background: rgba(15, 118, 110, 0.12);
            color: #0f3f3a;
            font-weight: 700;
            line-height: 1.1;
            white-space: nowrap;
            cursor: grab;
            pointer-events: auto;
            user-select: none;
        }

        .certificate-preview-state {
            position: absolute;
            inset: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.88);
            color: #5e6278;
            font-weight: 600;
            text-align: center;
        }
    </style>
@endpush
