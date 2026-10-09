@extends('layouts.admin')

@section('content')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="card">
                    <form method="POST" action="{{ route('admin.reminder_templates.save', $template->id ?? null) }}" id="kt_reminder_template_form">
                        @csrf
                        @if($template->id)
                            @method('PUT')
                        @endif

                        <div class="card-header">
                            <div class="card-title fs-3 fw-bolder">{{ $template->id ? 'Edit Template' : 'Add Template' }}</div>
                        </div>

                        <div class="card-body p-9">
                            <div class="row mb-6">
                                <label class="col-lg-3 col-form-label required fw-bold fs-6">Name</label>
                                <div class="col-lg-9 fv-row">
                                    <input type="text" name="name" value="{{ old('name', $template->name) }}" class="form-control form-control-lg form-control-solid" placeholder="Template name"/>
                                </div>
                            </div>

                            <div class="row mb-6">
                                <label class="col-lg-3 col-form-label required fw-bold fs-6">Channel</label>
                                <div class="col-lg-9 fv-row">
                                    <select name="channel" class="form-select form-select-lg form-select-solid" id="templateChannel">
                                        @foreach($channels as $channel)
                                            <option value="{{ $channel }}" {{ old('channel', $template->channel) === $channel ? 'selected' : '' }}>{{ ucfirst($channel) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-6">
                                <label class="col-lg-3 col-form-label required fw-bold fs-6">Reminder Timing</label>
                                <div class="col-lg-9 fv-row">
                                    <select name="timing" class="form-select form-select-lg form-select-solid">
                                        @foreach($timings as $key => $label)
                                            <option value="{{ $key }}" {{ old('timing', $template->timing) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-6" id="subjectWrap">
                                <label class="col-lg-3 col-form-label fw-bold fs-6">Email Subject</label>
                                <div class="col-lg-9 fv-row">
                                    <input type="text" name="subject" value="{{ old('subject', $template->subject) }}" class="form-control form-control-lg form-control-solid" placeholder="Email subject"/>
                                </div>
                            </div>

                            <div class="row mb-6">
                                <label class="col-lg-3 col-form-label required fw-bold fs-6">Message Body</label>
                                <div class="col-lg-9 fv-row">
                                    <textarea name="body" rows="10" class="form-control form-control-lg form-control-solid" placeholder="Message body">{{ old('body', $template->body) }}</textarea>
                                    <div class="form-text">
                                        Available placeholders:
                                        <code>@{{user_name}}</code>,
                                        <code>@{{user_email}}</code>,
                                        <code>@{{user_mobile}}</code>,
                                        <code>@{{module_title}}</code>,
                                        <code>@{{module_start}}</code>,
                                        <code>@{{module_url}}</code>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-6">
                                <label class="col-lg-3 col-form-label required fw-bold fs-6">Status</label>
                                <div class="col-lg-9 fv-row">
                                    <select name="status" class="form-select form-select-lg form-select-solid">
                                        <option value="active" {{ old('status', $template->status) === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status', $template->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer d-flex justify-content-end py-6 px-9">
                            <a href="{{ route('admin.reminder_templates') }}" class="btn btn-light btn-active-light-primary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save Template</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        KTUtil.onDOMContentLoaded(function () {
            const channel = document.querySelector('#templateChannel');
            const subjectWrap = document.querySelector('#subjectWrap');
            const syncSubject = () => subjectWrap?.classList.toggle('d-none', channel?.value !== 'email');
            channel?.addEventListener('change', syncSubject);
            syncSubject();
        });
    </script>
@endpush
