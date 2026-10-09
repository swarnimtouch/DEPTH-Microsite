<?php

namespace App\Http\Controllers\Admin;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CourseController
{
    public function index()
    {
        return view('admin.courses.index', [
            'title' => __('Programme'),
            'courses' => Course::orderByDesc('id')->get(),
            'breadcrumb' => breadcrumb([__('Programme') => route('admin.courses')]),
        ]);
    }

    public function addEditForm($id = null)
    {
        return view('admin.courses.add_edit', [
            'title' => __('Programme'),
            'course' => $id ? Course::findOrFail($id) : new Course(['status' => 'inactive']),
            'breadcrumb' => breadcrumb([__('Programme') => route('admin.courses'), ($id ? 'Edit' : 'Add Programme') => '']),
        ]);
    }

    public function save(Request $request, $id = null)
    {
        $course = $id ? Course::findOrFail($id) : new Course();
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'about' => ['required', 'string'],
            'speakers' => ['nullable', 'array'],
            'speakers.*.type' => ['nullable', Rule::in(['speaker', 'convenor', 'coordinator'])],
            'speakers.*.name' => ['nullable', 'string', 'max:255'],
            'speakers.*.designation' => ['nullable', 'string', 'max:255'],
            'speakers.*.hospital' => ['nullable', 'string', 'max:255'],
            'speakers.*.image' => ['nullable', 'string', 'max:255'],
            'speaker_images' => ['nullable', 'array'],
            'speaker_images.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $oldImages = collect($course->speakers ?? [])->pluck('image')->filter()->values();

        $validated['speakers'] = collect($validated['speakers'] ?? [])
            ->map(function ($speaker, $index) use ($request) {
                $image = $speaker['image'] ?? null;
                if ($request->hasFile("speaker_images.$index")) {
                    $image = $request->file("speaker_images.$index")->store('uploads/courses/people', 'public');
                }

                return [
                    'type' => $speaker['type'] ?? 'speaker',
                    'image' => $image,
                    'name' => trim($speaker['name'] ?? ''),
                    'designation' => trim($speaker['designation'] ?? ''),
                    'hospital' => trim($speaker['hospital'] ?? ''),
                ];
            })
            ->filter(fn($speaker) => $speaker['name'] !== '')
            ->values()
            ->all();

        $newImages = collect($validated['speakers'])->pluck('image')->filter()->values();
        $oldImages->diff($newImages)->each(fn($image) => $this->deleteFile($image));

        $validated['speakers'] = collect($validated['speakers'])
            ->map(fn($speaker) => [
                'type' => $speaker['type'],
                'image' => $speaker['image'],
                'name' => trim($speaker['name'] ?? ''),
                'designation' => trim($speaker['designation'] ?? ''),
                'hospital' => trim($speaker['hospital'] ?? ''),
            ])
            ->values()
            ->all();

        $course->fill($validated);

        if ($course->status === 'active') {
            Course::where('id', '!=', $course->id)->update(['status' => 'inactive']);
        }

        $course->save();

        if ($course->status === 'active') {
            Course::where('id', '!=', $course->id)->update(['status' => 'inactive']);
        }

        return redirect()->route('admin.courses')->with('success', 'Programme saved successfully');
    }

    public function statusChange($id)
    {
        $course = Course::findOrFail($id);
        if ($course->status === 'active') {
            $course->status = 'inactive';
        } else {
            Course::where('id', '!=', $course->id)->update(['status' => 'inactive']);
            $course->status = 'active';
        }
        $course->save();

        return response()->json(['success' => true]);
    }

    public function delete($id)
    {
        $course = Course::findOrFail($id);
        $this->deletePeopleImages($course);
        $course->delete();

        return response()->json(['success' => true, 'message' => 'Programme deleted successfully']);
    }

    public function deleteMultiple(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:courses,id'],
        ]);

        Course::whereIn('id', $validated['ids'])->get()->each(function ($course) {
            $this->deletePeopleImages($course);
            $course->delete();
        });

        return response()->json(['success' => true, 'message' => 'Programme deleted successfully']);
    }

    private function deletePeopleImages(Course $course): void
    {
        collect($course->speakers ?? [])->pluck('image')->filter()->each(fn($image) => $this->deleteFile($image));
    }

    private function deleteFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
