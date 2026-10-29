<?php

namespace App\Http\Controllers\Website;

use App\Support\CertificatePdf;
use App\Models\CertificateDownloadLog;
use App\Models\Comments;
use App\Models\Module;
use App\Models\ModuleViewSession;
use App\Models\Course;
use App\Models\ModuleUserProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\CarbonImmutable;

class ModuleController
{
    public function index()
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login')->with('error', 'Please login first');
        }

        $activeCourse = Course::active()->first();
        $modules = $activeCourse
            ? Module::active()->where('course_id', $activeCourse->id)->orderBy('sort_order')->orderBy('id')->get()
            : collect();

        return view('website.modules.index', [
            'title' => 'Programme Chapters',
            'activeCourse' => $activeCourse,
            'modules' => $modules,
            'progressByModule' => ModuleUserProgress::where('user_id', Auth::guard('web')->id())
                ->get()
                ->keyBy('module_id'),
        ]);
    }

    public function show($slug)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login')->with('error', 'Please login first');
        }

        $activeCourse = Course::active()->first();
        abort_if(!$activeCourse, 404);
        $module = Module::active()->with('resourceDocuments')->where('course_id', $activeCourse->id)->where('slug', $slug)->firstOrFail();

        if (!$module->isJoinOpen()) {
            return redirect()->route('dashboard.live')
                ->with('error', 'This chapter will open when the administrator enables it or its scheduled start time is reached.');
        }

        $nextModule = Module::active()
            ->where(function ($query) use ($module) {
                $query->where('sort_order', '>', $module->sort_order)
                    ->orWhere(function ($q) use ($module) {
                        $q->where('sort_order', $module->sort_order)
                            ->where('id', '>', $module->id);
                    });
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        return view('website.modules.show', [
            'title' => $module->title,
            'activeCourse' => $activeCourse,
            'module' => $module,
            'nextModule' => $nextModule,
            'progress' => ModuleUserProgress::firstOrCreate([
                'module_id' => $module->id,
                'user_id' => Auth::guard('web')->id(),
            ]),
        ]);
    }

    public function storeComment(Request $request, $slug)
    {
        if (!Auth::guard('web')->check()) {
            return response()->json(['message' => 'Please login first'], 401);
        }

        $activeCourse = Course::active()->first();
        abort_if(!$activeCourse, 404);

        $module = Module::active()->where('course_id', $activeCourse->id)->where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'comment' => ['required', 'string', 'max:1000'],
        ]);

        $comment = Comments::create([
            'episode_id' => $module->id,
            'user_id' => Auth::guard('web')->id(),
            'comment_id' => null,
            'comment' => trim($validated['comment']),
            'status' => 'publish',
        ])->load('user');

        if (!$request->expectsJson()) {
            return redirect()
                ->route('modules.show', $module->slug)
                ->with('success', 'Comment posted successfully.');
        }

        return response()->json([
            'success' => true,
            'comment' => $this->commentPayload($comment),
        ]);
    }

    private function commentPayload(Comments $comment): array
    {
        return [
            'id' => $comment->id,
            'name' => $comment->user?->name ?: 'Delegate',
            'initial' => mb_substr($comment->user?->name ?: 'D', 0, 1),
            'comment' => $comment->comment,
            'created_at' => $comment->created_at?->diffForHumans() ?: 'just now',
        ];
    }

    public function completeVideo(Request $request, $slug)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login')->with('error', 'Please login first');
        }

        $activeCourse = Course::active()->first();
        abort_if(!$activeCourse, 404);
        $module = Module::active()->where('course_id', $activeCourse->id)->where('slug', $slug)->firstOrFail();
        $progress = ModuleUserProgress::firstOrCreate([
            'module_id' => $module->id,
            'user_id' => Auth::guard('web')->id(),
        ]);
        $progress->video_completed_at = $progress->video_completed_at ?: now();
        $progress->recalculate();
        $progress->save();

        if ($request->expectsJson()) {
            $certificateUnlocked = (bool) $module->certificate_enabled;

            return response()->json([
                'success' => true,
                'message' => 'Video marked as completed',
                'quiz_url' => route('modules.quiz', $module->slug),
                'certificate_url' => $certificateUnlocked ? route('modules.certificate', $module->slug) : null,
                'progress_percent' => $progress->progress_percent,
                'completed_at' => $progress->video_completed_at?->format('d M Y, h:i A'),
                'certificate_unlocked' => $certificateUnlocked,
            ]);
        }

        return back()->with('success', 'Video marked as completed');
    }

    public function trackViewHeartbeat(Request $request, $slug)
    {
        if (!Auth::guard('web')->check()) {
            return response()->json(['message' => 'Please login first'], 401);
        }

        $activeCourse = Course::active()->first();
        abort_if(!$activeCourse, 404);

        $module = Module::active()->where('course_id', $activeCourse->id)->where('slug', $slug)->firstOrFail();
        $validated = $request->validate([
            'session_id' => ['required', 'string', 'max:80'],
            'elapsed_seconds' => ['nullable', 'integer', 'min:1', 'max:120'],
        ]);

        $now = CarbonImmutable::now('UTC');
        $elapsed = (int) ($validated['elapsed_seconds'] ?? 30);
        $intervalStart = $now->subSeconds($elapsed);
        $eventStart = $module->getRawOriginal('start_at')
            ? CarbonImmutable::parse($module->getRawOriginal('start_at'), 'UTC')
            : null;
        $eventEnd = $module->getRawOriginal('end_at')
            ? CarbonImmutable::parse($module->getRawOriginal('end_at'), 'UTC')
            : null;

        $duringSeconds = $eventStart && $eventEnd
            ? $this->overlapSeconds($intervalStart, $now, $eventStart, $eventEnd)
            : 0;
        $postSeconds = $eventEnd && $now->gt($eventEnd)
            ? $this->overlapSeconds($intervalStart, $now, $eventEnd, $now)
            : 0;

        $session = ModuleViewSession::firstOrCreate(
            [
                'module_id' => $module->id,
                'user_id' => Auth::guard('web')->id(),
                'session_id' => $validated['session_id'],
            ],
            [
                'started_at' => $intervalStart,
                'last_seen_at' => $now,
            ]
        );

        $session->last_seen_at = $now;
        $session->total_seconds += $elapsed;
        $session->during_event_seconds += $duringSeconds;
        $session->post_event_seconds += $postSeconds;
        $session->save();

        return response()->json(['success' => true]);
    }

    private function overlapSeconds(CarbonImmutable $start, CarbonImmutable $end, CarbonImmutable $windowStart, CarbonImmutable $windowEnd): int
    {
        $overlapStart = $start->greaterThan($windowStart) ? $start : $windowStart;
        $overlapEnd = $end->lessThan($windowEnd) ? $end : $windowEnd;

        return $overlapEnd->gt($overlapStart) ? $overlapStart->diffInSeconds($overlapEnd) : 0;
    }

    public function quiz($slug)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login')->with('error', 'Please login first');
        }

        $activeCourse = Course::active()->first();
        abort_if(!$activeCourse, 404);
        $module = Module::active()->where('course_id', $activeCourse->id)->where('slug', $slug)->firstOrFail();

//        if (!$module->isAvailable()) {
//            return redirect()->route('modules')->with('error', 'This chapter is not available for today');
//        }

        $progress = ModuleUserProgress::firstOrCreate([
            'module_id' => $module->id,
            'user_id' => Auth::guard('web')->id(),
        ]);

        if (!$progress->video_completed_at) {
            return redirect()->route('modules.show', $module->slug)->with('error', 'Please complete the video first');
        }

        $questions = $module->activeQuestions()->with('options')->get();
        if ($questions->isEmpty()) {
            return redirect()->route('modules.show', $module->slug)->with('error', 'Feedback is not available yet');
        }

        return view('website.modules.quiz', [
            'title' => $module->title . ' Feedback',
            'module' => $module,
            'questions' => $questions,
            'progress' => $progress,
        ]);
    }

    public function submitQuiz(Request $request, $slug)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login')->with('error', 'Please login first');
        }

        $activeCourse = Course::active()->first();
        abort_if(!$activeCourse, 404);
        $module = Module::active()->where('course_id', $activeCourse->id)->where('slug', $slug)->firstOrFail();

        $questions = $module->activeQuestions()->with('options')->get();

        $validated = $request->validate([
            'answers' => ['required', 'array'],
        ]);

        $answerLog = [];

        foreach ($questions as $question) {
            if (($question->question_type ?? 'options') === 'text') {
                $answerText = trim((string) ($validated['answers'][$question->id] ?? ''));

                if ($answerText === '') {
                    return back()
                        ->withInput()
                        ->withErrors(['answers' => 'Please answer all feedback questions.']);
                }

                $answerLog[$question->id] = [
                    'answer_text' => $answerText,
                ];

                continue;
            }

            $selectedOptionId = $validated['answers'][$question->id] ?? null;
            $validOptionIds = $question->options->pluck('id')->map(fn($id) => (int) $id);

            if (!$selectedOptionId || !$validOptionIds->contains((int) $selectedOptionId)) {
                return back()
                    ->withInput()
                    ->withErrors(['answers' => 'Please answer all feedback questions.']);
            }

            $answerLog[$question->id] = [
                'selected_option_id' => (int) $selectedOptionId,
            ];
        }

        $progress = ModuleUserProgress::firstOrCreate([
            'module_id' => $module->id,
            'user_id' => Auth::guard('web')->id(),
        ]);
        $progress->quiz_completed_at = now();
        $progress->quiz_score = 0;
        $progress->quiz_total = $questions->count();
        $progress->answers = $answerLog;
        $progress->recalculate();
        $progress->save();

        return redirect()->route('modules.show', $module->slug)
            ->with('success', 'Feedback submitted successfully.');
    }

    public function certificate($slug)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login')->with('error', 'Please login first');
        }

        $activeCourse = Course::active()->first();
        abort_if(!$activeCourse, 404);

        $module = Module::active()->where('course_id', $activeCourse->id)->where('slug', $slug)->firstOrFail();

        if (!$module->certificate_enabled) {
            return redirect()
                ->route('modules.show', $module->slug)
                ->with('error', 'Certificate downloads are not available yet');
        }

        $templatePath = $module->certificate_template
            ? Storage::disk('public')->path($module->certificate_template)
            : resource_path('certificates/delegate_certificate.pdf');
        abort_if(!is_file($templatePath), 404, 'Certificate template not found');

        $doctor = Auth::guard('web')->user();
        $pdf = CertificatePdf::delegateCertificate(
            $templatePath,
            $doctor->name ?? 'Delegate',
            $module->certificate_name_x !== null ? (float) $module->certificate_name_x : null,
            $module->certificate_name_y !== null ? (float) $module->certificate_name_y : null,
            $module->certificate_font_size ?: null,
            public_path('assets/fonts/Abril_Display_Italic.otf'),
        );
        $fileName = Str::slug(($doctor->name ?? 'delegate') . '-' . $module->title . '-certificate') . '.pdf';

        CertificateDownloadLog::create([
            'module_id' => $module->id,
            'user_id' => $doctor->id,
            'downloaded_at' => now(),
        ]);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Content-Length' => strlen($pdf),
        ]);
    }
}
