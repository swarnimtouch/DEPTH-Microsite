<?php

namespace App\Http\Controllers\Admin;

use App\Models\Module;
use App\Models\ModuleResource;
use App\Models\Course;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ModuleController
{
    public function index()
    {
        return view('admin.modules.index', [
            'title' => __('Chapters'),
            'breadcrumb' => breadcrumb([__('Chapters') => route('admin.modules')]),
        ]);
    }

    public function datatable(Request $request)
    {
        $query = Module::query();

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%$search%");
                $q->orWhere('speaker_name', 'LIKE', "%$search%");
                $q->orWhere('duration', 'LIKE', "%$search%");
            });
        }

        if ($request->has('order')) {
            $columns = $request->columns;
            foreach ($request->order as $order) {
                $columnIndex = $order['column'];
                $columnName = $columns[$columnIndex]['data'];
                $direction = $order['dir'];

                $dbColumn = match ($columnName) {
                    'title' => 'title',
                    'speaker_name' => 'speaker_name',
                    'sort_order' => 'sort_order',
                    'start_date' => 'start_date',
                    'end_date' => 'end_date',
                    'status' => 'status',
                    default => 'id'
                };

                $query->orderBy($dbColumn, $direction);
            }
        } else {
            $query->orderBy('sort_order')->orderBy('id');
        }

        $recordsTotal = $query->count();
        $data = $query
            ->skip($request->start)
            ->take($request->length)
            ->get();

        return response()->json([
            "draw" => intval($request->draw),
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsTotal,
            "data" => $data,
        ]);
    }

    public function addEditForm($id = null)
    {
        $module = $id ? Module::with('resourceDocuments')->findOrFail($id) : new Module(['status' => 'active']);

        return view('admin.modules.add_edit', [
            'module' => $module,
            'courses' => Course::orderBy('title')->get(),
            'title' => __('Chapters'),
            'breadcrumb' => breadcrumb([__('Chapters') => route('admin.modules'), ($id ? 'Edit' : 'Add Chapter') => '']),
        ]);
    }

    public function agendaForm($id)
    {
        $module = Module::findOrFail($id);

        return view('admin.modules.agenda', [
            'module' => $module,
            'title' => __('Chapter Agenda'),
            'breadcrumb' => breadcrumb([
                __('Chapters') => route('admin.modules'),
                $module->title => route('admin.modules.add_edit_form', $module->id),
                __('Agenda') => '',
            ]),
        ]);
    }

    public function certificateTemplatePreview($id = null)
    {
        $module = $id ? Module::findOrFail($id) : null;
        $path = $module?->certificate_template
            ? Storage::disk('public')->path($module->certificate_template)
            : resource_path('certificates/delegate_certificate.pdf');

        abort_if(!is_file($path), 404, 'Certificate template not found');

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="certificate-template.pdf"',
        ]);
    }

    public function save(Request $request, $id = null)
    {
        $module = $id ? Module::findOrFail($id) : new Module();
        $thumbnailRule = (!$module->thumbnail || $request->thumbnail_remove == 1) ? 'required' : 'nullable';

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'venue' => ['nullable', 'string', 'max:255'],
            'course_id' => ['required', Rule::exists('courses', 'id')],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('modules', 'slug')->ignore($id)],
            'doctor_id' => ['nullable', Rule::exists('users', 'id')->where('type', 'doctor')],
            'speaker_designation' => ['nullable', 'string', 'max:255'],
            'speakers' => ['required', 'array', 'min:1'],
            'speakers.*.name' => ['required', 'string', 'max:255'],
            'speakers.*.role' => ['nullable', 'string', 'max:100'],
            'speakers.*.designation' => ['required', 'string', 'max:255'],
            'speakers.*.current_position' => ['nullable', 'string', 'max:255'],
            'speakers.*.hospital' => ['nullable', 'string', 'max:255'],
            'speakers.*.country' => ['nullable', 'string', 'max:100'],
            'speakers.*.city' => ['nullable', 'string', 'max:100'],
            'speakers.*.bio' => ['nullable', 'string', 'max:5000'],
            'speakers.*.professional_profile' => ['nullable', 'string', 'max:5000'],
            'speakers.*.expertise' => ['nullable', 'string', 'max:1000'],
            'speakers.*.session_title' => ['nullable', 'string', 'max:255'],
            'speakers.*.profile_resource_url' => ['nullable', 'url', 'max:2048'],
            'speakers.*.image' => ['nullable', 'string', 'max:255'],
            'speaker_images' => ['nullable', 'array'],
            'speaker_images.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'brands' => ['nullable', 'array'],
            'brands.*.label' => ['nullable', 'string', 'max:255'],
            'brands.*.logo' => ['nullable', 'string', 'max:255'],
            'brand_crops' => ['nullable', 'array'],
            'brand_crops.*.x' => ['nullable', 'numeric'],
            'brand_crops.*.y' => ['nullable', 'numeric'],
            'brand_crops.*.width' => ['nullable', 'numeric'],
            'brand_crops.*.height' => ['nullable', 'numeric'],
            'brand_logos' => ['nullable', 'array'],
            'brand_logos.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'duration' => ['required', 'string', 'max:50'],
            'cme_credit' => ['required', 'numeric', 'min:0', 'max:99.9'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'certificate_template' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'certificate_template_remove' => ['nullable', 'boolean'],
            'certificate_name_x' => ['nullable', 'numeric', 'min:0', 'max:2000'],
            'certificate_name_y' => ['nullable', 'numeric', 'min:0', 'max:2000'],
            'certificate_font_size' => ['nullable', 'integer', 'min:8', 'max:72'],
            'video_url' => [
                'nullable',
                'url',
                'regex:/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\//i',
            ],
            'join_url' => ['nullable', 'url', 'max:2048'],
            'overview' => ['required', 'string'],
            'key_takeaways' => ['nullable', 'string'],
            'resource_labels' => ['nullable', 'array'],
            'resource_labels.*' => ['nullable', 'string', 'max:255'],
            'resource_categories' => ['nullable', 'array'],
            'resource_categories.*' => ['nullable', Rule::in(['full_recording', 'post_read', 'snippet_video', 'case_book', 'general'])],
            'resource_descriptions' => ['nullable', 'array'],
            'resource_descriptions.*' => ['nullable', 'string', 'max:2000'],
            'resource_types' => ['nullable', 'array'],
            'resource_types.*' => ['nullable', Rule::in(['auto', 'pdf', 'video', 'url'])],
            'resource_available_at' => ['nullable', 'array'],
            'resource_available_at.*' => ['nullable', 'date'],
            'resource_statuses' => ['nullable', 'array'],
            'resource_statuses.*' => ['nullable', Rule::in(['active', 'inactive'])],
            'resource_urls' => ['nullable', 'array'],
            'resource_urls.*' => ['nullable', 'url', 'max:2048'],
            'resource_files' => ['nullable', 'array'],
            'resource_files.*' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png,webp,mp4,webm,mov', 'max:102400'],
            'resource_thumbnails' => ['nullable', 'array'],
            'resource_thumbnails.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'existing_resource_labels' => ['nullable', 'array'],
            'existing_resource_labels.*' => ['nullable', 'string', 'max:255'],
            'existing_resource_categories' => ['nullable', 'array'],
            'existing_resource_categories.*' => ['nullable', Rule::in(['full_recording', 'post_read', 'snippet_video', 'case_book', 'general'])],
            'existing_resource_descriptions' => ['nullable', 'array'],
            'existing_resource_descriptions.*' => ['nullable', 'string', 'max:2000'],
            'existing_resource_types' => ['nullable', 'array'],
            'existing_resource_types.*' => ['nullable', Rule::in(['auto', 'pdf', 'video', 'url'])],
            'existing_resource_available_at' => ['nullable', 'array'],
            'existing_resource_available_at.*' => ['nullable', 'date'],
            'existing_resource_statuses' => ['nullable', 'array'],
            'existing_resource_statuses.*' => ['nullable', Rule::in(['active', 'inactive'])],
            'existing_resource_urls' => ['nullable', 'array'],
            'existing_resource_urls.*' => ['nullable', 'url', 'max:2048'],
            'existing_resource_files' => ['nullable', 'array'],
            'existing_resource_files.*' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png,webp,mp4,webm,mov', 'max:102400'],
            'existing_resource_thumbnails' => ['nullable', 'array'],
            'existing_resource_thumbnails.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_resources' => ['nullable', 'array'],
            'remove_resources.*' => ['integer', Rule::exists('module_resources', 'id')],
            'sort_order' => ['required', 'integer', 'min:0'],
            'join_enabled' => ['required', 'boolean'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after_or_equal:start_at'],
        ], [
            'video_url.regex' => 'Please enter a valid YouTube link.',
        ]);

        $oldSpeakerImages = collect($module->speakers ?? [])->pluck('image')->filter()->values();
        $speakerProfiles = collect($validated['speakers'] ?? [])
            ->map(function ($speaker, $index) use ($request) {
                $image = $speaker['image'] ?? null;
                if ($request->hasFile("speaker_images.$index")) {
                    $image = $request->file("speaker_images.$index")->store('uploads/speakers', 'public');
                }

                return [
                    'name' => trim($speaker['name'] ?? ''),
                    'role' => trim($speaker['role'] ?? '') ?: 'Speaker',
                    'designation' => trim($speaker['designation'] ?? ''),
                    'current_position' => trim($speaker['current_position'] ?? ''),
                    'hospital' => trim($speaker['hospital'] ?? ''),
                    'country' => trim($speaker['country'] ?? ''),
                    'city' => trim($speaker['city'] ?? ''),
                    'bio' => trim($speaker['bio'] ?? ''),
                    'professional_profile' => trim($speaker['professional_profile'] ?? ''),
                    'expertise' => trim($speaker['expertise'] ?? ''),
                    'session_title' => trim($speaker['session_title'] ?? ''),
                    'profile_resource_url' => trim($speaker['profile_resource_url'] ?? ''),
                    'image' => $image,
                ];
            })
            ->filter(fn($speaker) => $speaker['name'] !== '')
            ->values();

        $newSpeakerImages = $speakerProfiles->pluck('image')->filter()->values();
        $oldSpeakerImages->diff($newSpeakerImages)->each(fn($image) => $this->deleteFile($image));

        $oldBrandLogos = collect($module->brands ?? [])->pluck('logo')->filter()->values();
        $moduleBrands = collect($validated['brands'] ?? [])
            ->map(function ($brand, $index) use ($request) {
                $logo = $brand['logo'] ?? null;
                if ($request->hasFile("brand_logos.$index")) {
                    $logo = $this->cropAndStoreBrandLogo(
                        $request->file("brand_logos.$index"),
                        $request->input("brand_crops.$index", [])
                    );
                }

                return [
                    'label' => trim($brand['label'] ?? ''),
                    'logo' => $logo,
                ];
            })
            ->filter(fn($brand) => $brand['label'] !== '' || $brand['logo'])
            ->values();

        $newBrandLogos = $moduleBrands->pluck('logo')->filter()->values();
        $oldBrandLogos->diff($newBrandLogos)->each(fn($logo) => $this->deleteFile($logo));

        $resourceLabels = collect($request->input('resource_labels', []))
            ->filter(fn($label) => trim((string)$label) !== '');
        $removedResourceIds = collect($request->input('remove_resources', []))->map(fn($id) => (int) $id);
        foreach ($request->input('existing_resource_labels', []) as $resourceId => $label) {
            if ($removedResourceIds->contains((int) $resourceId)) {
                continue;
            }

            if (trim((string) $label) === '') {
                return back()->withInput()->withErrors(['resources' => 'Please enter a label for every existing resource.']);
            }

            // Existing resources may intentionally remain as "Coming soon" placeholders.
            // Requiring a source here also blocks unrelated edits such as speaker details.
        }

        foreach ($request->file('resource_files', []) as $index => $file) {
            if (!$resourceLabels->has($index)) {
                return back()->withInput()->withErrors(['resources' => 'Please enter a label for every resource document.']);
            }
        }
        foreach ($resourceLabels as $index => $label) {
            if (!$request->hasFile("resource_files.$index") && !$request->filled("resource_urls.$index")) {
                return back()->withInput()->withErrors(['resources' => 'Please upload a file/video or enter an external URL for every resource.']);
            }
        }

        $startAtUtc = CarbonImmutable::parse($validated['start_at'], 'UTC');
        $endAtUtc = CarbonImmutable::parse($validated['end_at'], 'UTC');

        $moduleData = collect($validated)
            ->except(['start_at', 'end_at', 'certificate_template', 'certificate_template_remove', 'resource_labels', 'resource_categories', 'resource_descriptions', 'resource_types', 'resource_available_at', 'resource_statuses', 'resource_urls', 'resource_files', 'resource_thumbnails', 'existing_resource_labels', 'existing_resource_categories', 'existing_resource_descriptions', 'existing_resource_types', 'existing_resource_available_at', 'existing_resource_statuses', 'existing_resource_urls', 'existing_resource_files', 'existing_resource_thumbnails', 'remove_resources', 'speakers', 'speaker_images', 'brand_logos', 'brand_crops'])
            ->toArray();

        $moduleData['duration'] = trim($validated['duration']);

        foreach (['certificate_name_x', 'certificate_name_y', 'certificate_font_size'] as $nullableNumberField) {
            if (array_key_exists($nullableNumberField, $moduleData) && $moduleData[$nullableNumberField] === '') {
                $moduleData[$nullableNumberField] = null;
            }
        }

        $module->fill($moduleData);
        $module->doctor_id = null;
        $module->speakers = $speakerProfiles->all();
        $primarySpeaker = $speakerProfiles->first();
        $module->speaker_name = $primarySpeaker['name'] ?? null;
        $module->speaker_designation = $primarySpeaker['designation'] ?? null;
        $module->speaker_hospital = $primarySpeaker['hospital'] ?? null;
        $module->slug = $validated['slug'] ?: Module::uniqueSlug($validated['title'], $id);
        $module->brands = $moduleBrands->map(fn($brand) => [
            'label' => $brand['label'],
            'logo' => $brand['logo'],
        ])->values()->all();
        $module->cme_credit = $validated['cme_credit'];
        $module->sort_order = $validated['sort_order'];
        $module->start_at = $startAtUtc->format('Y-m-d H:i:s');
        $module->end_at = $endAtUtc->format('Y-m-d H:i:s');
        $module->start_date = $startAtUtc->toDateString();
        $module->end_date = $endAtUtc->toDateString();
        $module->is_locked = false;
        $module->status = $module->status ?: 'active';

        if ($request->thumbnail_remove == 1) {
            $this->deleteThumbnail($module);
            $module->thumbnail = null;
        }

        if ($request->hasFile('thumbnail')) {
            $this->deleteThumbnail($module);
            $module->thumbnail = $request->file('thumbnail')->store('uploads/modules', 'public');
        }

        if ($request->boolean('certificate_template_remove')) {
            $this->deleteCertificateTemplate($module);
            $module->certificate_template = null;
        }

        if ($request->hasFile('certificate_template')) {
            $this->deleteCertificateTemplate($module);
            $module->certificate_template = $request->file('certificate_template')->store('uploads/certificates', 'public');
        }

        $module->save();
        $this->syncResources($module, $request);

        return redirect()->route('admin.modules')
            ->with('success', 'Chapter Saved Successfully');
    }

    public function saveAgenda(Request $request, $id)
    {
        $module = Module::findOrFail($id);

        $validated = $request->validate([
            'agenda' => ['nullable', 'array'],
            'agenda.*.type' => ['nullable', Rule::in(['section', 'person'])],
            'agenda.*.time' => ['nullable', 'date_format:H:i'],
            'agenda.*.duration' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'agenda.*.title' => ['nullable', 'string', 'max:255'],
            'agenda.*.role' => ['nullable', 'string', 'max:255'],
            'agenda.*.names' => ['nullable', 'string', 'max:1000'],
            'archive_segments' => ['nullable', 'array'],
            'archive_segments.*.topic' => ['nullable', 'string', 'max:255'],
            'archive_segments.*.youtube_url' => [
                'nullable',
                'url',
                'regex:/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\//i',
            ],
            'archive_segments.*.start_time' => ['nullable', 'string', 'max:20'],
        ], [
            'archive_segments.*.youtube_url.regex' => 'Please enter a valid YouTube link for every archive segment.',
        ]);

        $agendaRows = collect($validated['agenda'] ?? [])
            ->map(fn($row) => [
                'type' => $row['type'] ?? 'person',
                'time' => trim($row['time'] ?? ''),
                'duration' => isset($row['duration']) && $row['duration'] !== '' ? (int) $row['duration'] : null,
                'title' => trim($row['title'] ?? ''),
                'role' => trim($row['role'] ?? ''),
                'names' => trim($row['names'] ?? ''),
            ])
            ->filter(fn($row) => $row['title'] !== '' || $row['role'] !== '' || $row['names'] !== '')
            ->values();

        $archiveSegments = collect($validated['archive_segments'] ?? [])
            ->map(fn($segment) => [
                'topic' => trim($segment['topic'] ?? ''),
                'youtube_url' => trim($segment['youtube_url'] ?? ''),
                'start_time' => trim($segment['start_time'] ?? ''),
            ])
            ->filter(fn($segment) => $segment['topic'] !== '' || $segment['youtube_url'] !== '')
            ->values();

        $module->agenda = $agendaRows->all();
        $module->archive_segments = $archiveSegments->all();
        $module->save();

        return redirect()->route('admin.modules')
            ->with('success', 'Chapter agenda saved successfully');
    }

    public function statusChange($id)
    {
        try {
            $module = Module::findOrFail($id);
            $module->status = $module->status === 'active' ? 'inactive' : 'active';
            $module->save();

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating status',
            ], 500);
        }
    }

    public function delete($id)
    {
        try {
            $module = Module::findOrFail($id);
            $this->deleteThumbnail($module);
            $this->deleteResources($module);
            $this->deleteSpeakerImages($module);
            $this->deleteBrandLogos($module);
            $this->deleteCertificateTemplate($module);
            $module->delete();

            return response()->json([
                'success' => true,
                'message' => 'Chapter deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "You can't delete this record",
            ], 500);
        }
    }

    public function deleteMultiple(Request $request)
    {
        try {
            $ids = $request->input('ids', []);

            if (empty($ids)) {
                return response()->json(['success' => false, 'message' => 'No data selected'], 400);
            }

            Module::whereIn('id', $ids)->get()->each(function ($module) {
                $this->deleteThumbnail($module);
                $this->deleteResources($module);
                $this->deleteSpeakerImages($module);
                $this->deleteBrandLogos($module);
                $this->deleteCertificateTemplate($module);
                $module->delete();
            });

            return response()->json(['success' => true, 'message' => 'Chapters deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deleting chapters'], 500);
        }
    }

    private function deleteThumbnail(Module $module): void
    {
        $this->deleteFile($module->thumbnail);
    }

    private function deleteSpeakerImages(Module $module): void
    {
        collect($module->speakers ?? [])->pluck('image')->filter()->each(fn($image) => $this->deleteFile($image));
    }

    private function deleteBrandLogos(Module $module): void
    {
        collect($module->brands ?? [])->pluck('logo')->filter()->each(fn($logo) => $this->deleteFile($logo));
    }

    private function deleteCertificateTemplate(Module $module): void
    {
        $this->deleteFile($module->certificate_template);
    }

    private function cropAndStoreBrandLogo($file, array $crop): string
    {
        [$sourceWidth, $sourceHeight, $sourceType] = getimagesize($file->getRealPath());

        $source = match ($sourceType) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($file->getRealPath()),
            IMAGETYPE_PNG => imagecreatefrompng($file->getRealPath()),
            IMAGETYPE_WEBP => imagecreatefromwebp($file->getRealPath()),
            default => null,
        };

        if (!$source) {
            return $file->store('uploads/modules/brands', 'public');
        }

        $targetWidth = 260;
        $targetHeight = 80;

        $cropWidth = max(1, (int) round($crop['width'] ?? 0));
        $cropHeight = max(1, (int) round($crop['height'] ?? 0));
        $cropX = max(0, (int) round($crop['x'] ?? 0));
        $cropY = max(0, (int) round($crop['y'] ?? 0));

        if ($cropWidth <= 1 || $cropHeight <= 1) {
            $cropWidth = $sourceWidth;
            $cropHeight = $sourceHeight;
            $cropX = 0;
            $cropY = 0;
        }

        $cropWidth = min($cropWidth, $sourceWidth - $cropX);
        $cropHeight = min($cropHeight, $sourceHeight - $cropY);

        $target = imagecreatetruecolor($targetWidth, $targetHeight);
        $white = imagecolorallocate($target, 255, 255, 255);
        imagefilledrectangle($target, 0, 0, $targetWidth, $targetHeight, $white);
        $scale = min($targetWidth / $cropWidth, $targetHeight / $cropHeight);
        $drawWidth = (int) round($cropWidth * $scale);
        $drawHeight = (int) round($cropHeight * $scale);
        $drawX = (int) round(($targetWidth - $drawWidth) / 2);
        $drawY = (int) round(($targetHeight - $drawHeight) / 2);
        imagecopyresampled($target, $source, $drawX, $drawY, $cropX, $cropY, $drawWidth, $drawHeight, $cropWidth, $cropHeight);

        $path = 'uploads/modules/brands/brand_' . uniqid() . '.jpg';
        Storage::disk('public')->makeDirectory('uploads/modules/brands');
        imagejpeg($target, Storage::disk('public')->path($path), 90);

        imagedestroy($source);
        imagedestroy($target);

        return $path;
    }

    private function deleteFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function syncResources(Module $module, Request $request): void
    {
        $removeIds = $request->input('remove_resources', []);
        if (!empty($removeIds)) {
            $module->resourceDocuments()->whereIn('id', $removeIds)->get()->each(function (ModuleResource $resource) {
                $this->deleteResourceAssets($resource);
                $resource->delete();
            });
        }

        $existingLabels = $request->input('existing_resource_labels', []);
        foreach ($existingLabels as $resourceId => $label) {
            if (in_array((int) $resourceId, array_map('intval', $removeIds), true)) {
                continue;
            }

            $resource = $module->resourceDocuments()->where('id', $resourceId)->first();
            if (!$resource) {
                continue;
            }

            $resource->label = trim((string) $label);
            $resource->description = trim((string) $request->input("existing_resource_descriptions.$resourceId", ''));
            $resource->category = $request->input("existing_resource_categories.$resourceId", 'general');
            $resource->resource_type = $request->input("existing_resource_types.$resourceId", 'auto');
            $resource->available_at = $request->filled("existing_resource_available_at.$resourceId")
                ? CarbonImmutable::parse($request->input("existing_resource_available_at.$resourceId"), 'Asia/Kolkata')->utc()
                : null;
            $resource->status = $request->input("existing_resource_statuses.$resourceId", 'active');
            $externalUrl = trim((string) $request->input("existing_resource_urls.$resourceId", ''));

            if ($request->hasFile("existing_resource_thumbnails.$resourceId")) {
                $this->deleteFile($resource->thumbnail);
                $resource->thumbnail = $request->file("existing_resource_thumbnails.$resourceId")
                    ->store('uploads/module-resources/thumbnails', 'public');
            }

            if ($request->hasFile("existing_resource_files.$resourceId")) {
                $this->deleteResourceFile($resource);
                $file = $request->file("existing_resource_files.$resourceId");
                $resource->file = $file->store('uploads/module-resources', 'public');
                $resource->file_type = $file->getClientOriginalExtension();
                $resource->external_url = null;
            } elseif ($externalUrl !== '') {
                $this->deleteResourceFile($resource);
                $resource->file = null;
                $resource->file_type = pathinfo(parse_url($externalUrl, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION) ?: 'url';
                $resource->external_url = $externalUrl;
            }

            $resource->save();
        }

        foreach ($request->input('resource_labels', []) as $index => $resourceLabel) {
            $label = trim((string) $resourceLabel);
            if ($label === '') {
                continue;
            }

            $file = $request->file("resource_files.$index");
            $thumbnail = $request->file("resource_thumbnails.$index");
            $externalUrl = trim((string) $request->input("resource_urls.$index", ''));
            $path = $file?->store('uploads/module-resources', 'public');

            $module->resourceDocuments()->create([
                'label' => $label,
                'description' => trim((string) $request->input("resource_descriptions.$index", '')),
                'category' => $request->input("resource_categories.$index", 'general'),
                'resource_type' => $request->input("resource_types.$index", 'auto'),
                'file' => $path,
                'external_url' => $file ? null : $externalUrl,
                'thumbnail' => $thumbnail?->store('uploads/module-resources/thumbnails', 'public'),
                'file_type' => $file?->getClientOriginalExtension()
                    ?: (pathinfo(parse_url($externalUrl, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION) ?: 'url'),
                'available_at' => $request->filled("resource_available_at.$index")
                    ? CarbonImmutable::parse($request->input("resource_available_at.$index"), 'Asia/Kolkata')->utc()
                    : null,
                'status' => $request->input("resource_statuses.$index", 'active'),
                'sort_order' => $index,
            ]);
        }
    }

    private function deleteResources(Module $module): void
    {
        $module->resourceDocuments()->get()->each(function (ModuleResource $resource) {
            $this->deleteResourceAssets($resource);
        });
    }

    private function deleteResourceAssets(ModuleResource $resource): void
    {
        $this->deleteResourceFile($resource);
        $this->deleteFile($resource->thumbnail);
    }

    private function deleteResourceFile(ModuleResource $resource): void
    {
        if ($resource->file && Storage::disk('public')->exists($resource->file)) {
            Storage::disk('public')->delete($resource->file);
        }
    }
}
