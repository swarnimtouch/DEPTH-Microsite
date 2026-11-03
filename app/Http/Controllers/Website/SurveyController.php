<?php

namespace App\Http\Controllers\Website;

use App\Models\Course;
use App\Models\Module;
use App\Models\ModuleUserProgress;
use App\Models\SurveySubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SurveyController
{
    public function state($slug)
    {
        abort_unless(Auth::guard('web')->check(), 401, 'Please login first.');
        $module = $this->module($slug);

        return response()->json(static::moduleState($module, Auth::guard('web')->id()));
    }

    public static function moduleState(Module $module, int $userId): array
    {
        $progress = ModuleUserProgress::where('module_id', $module->id)->where('user_id', $userId)->first();

        return [
            'pre' => static::surveyState($module, $userId, 'pre', true),
            'post' => static::surveyState($module, $userId, 'post', (bool) $progress?->video_completed_at),
            'certificate_enabled' => (bool) $module->certificate_enabled,
            'certificate_url' => $module->certificate_enabled ? route('modules.certificate', $module->slug) : null,
        ];
    }

    public function submit(Request $request, $slug, string $type)
    {
        abort_unless(Auth::guard('web')->check(), 401, 'Please login first.');
        abort_unless(in_array($type, ['pre', 'post'], true), 404);
        $module = $this->module($slug);
        abort_unless($module->surveyQuestions()->active()->where('survey_type', $type)->exists(), 422, 'This survey is not active.');

        if ($type === 'post') {
            $progress = ModuleUserProgress::where('module_id', $module->id)->where('user_id', Auth::guard('web')->id())->first();
            abort_unless($progress?->video_completed_at, 422, 'Please complete the module before taking the post-event survey.');
        }

        $questions = $module->surveyQuestions()->active()->where('survey_type', $type)->with('options')->get();
        abort_if($questions->isEmpty(), 422, 'No active questions are available.');
        $validated = $request->validate(['answers' => ['required', 'array']]);

        foreach ($questions as $question) {
            $optionId = $validated['answers'][$question->id] ?? null;
            abort_unless($question->options->contains('id', (int) $optionId), 422, 'Please answer every survey question.');
        }

        $submission = DB::transaction(function () use ($module, $questions, $validated, $type) {
            $submission = SurveySubmission::firstOrCreate(
                ['module_id' => $module->id, 'user_id' => Auth::guard('web')->id(), 'survey_type' => $type],
                ['submitted_at' => now()]
            );

            if (! $submission->wasRecentlyCreated) {
                return $submission;
            }

            foreach ($questions as $index => $question) {
                $option = $question->options->firstWhere('id', (int) $validated['answers'][$question->id]);
                $submission->answers()->create([
                    'survey_question_id' => $question->id,
                    'survey_option_id' => $option->id,
                    'question_text' => $question->question,
                    'answer_text' => $option->option_text,
                    'sort_order' => $index,
                ]);
            }

            return $submission;
        });

        return response()->json([
            'success' => true,
            'already_submitted' => ! $submission->wasRecentlyCreated,
            'message' => $type === 'pre' ? 'Pre-event responses saved.' : 'Thank you. Your post-event survey has been submitted.',
            'certificate_enabled' => (bool) $module->certificate_enabled,
            'certificate_url' => $type === 'post' && $module->certificate_enabled ? route('modules.certificate', $module->slug) : null,
        ]);
    }

    public function responses($slug, string $type)
    {
        abort_unless(Auth::guard('web')->check(), 401, 'Please login first.');
        abort_unless(in_array($type, ['pre', 'post'], true), 404);
        $module = $this->module($slug);
        $submission = SurveySubmission::with('answers.question.options')
            ->where('module_id', $module->id)
            ->where('user_id', Auth::guard('web')->id())
            ->where('survey_type', $type)
            ->firstOrFail();

        return view('website.modules.survey_responses', [
            'title' => ucfirst($type).'-event Survey Responses',
            'module' => $module,
            'submission' => $submission,
            'surveyType' => $type,
        ]);
    }

    private static function surveyState(Module $module, int $userId, string $type, bool $eligible): array
    {
        $enabled = $module->surveyQuestions()->active()->where('survey_type', $type)->exists();
        $submission = SurveySubmission::with('answers.question.options')
            ->where('module_id', $module->id)->where('user_id', $userId)->where('survey_type', $type)->first();
        $questions = $enabled && ! $submission
            ? $module->surveyQuestions()->active()->where('survey_type', $type)->with('options')->get()
            : collect();

        return [
            'enabled' => $enabled,
            'eligible' => $eligible,
            'completed' => (bool) $submission,
            'submitted_at' => $submission?->submitted_at?->format('d M Y, h:i A'),
            'questions' => $questions->map(fn ($question) => [
                'id' => $question->id,
                'question' => $question->question,
                'options' => $question->options->map(fn ($option) => ['id' => $option->id, 'text' => $option->option_text])->values(),
            ])->values(),
            'responses' => $submission?->answers->map(function ($answer) {
                $options = $answer->question?->options?->pluck('option_text')->values() ?? collect();
                if (! $options->contains($answer->answer_text)) {
                    $options->push($answer->answer_text);
                }

                return [
                    'question' => $answer->question_text,
                    'answer' => $answer->answer_text,
                    'options' => $options->values(),
                ];
            })->values() ?? [],
            'submit_url' => route('modules.surveys.submit', ['slug' => $module->slug, 'type' => $type]),
            'response_url' => $submission ? route('modules.surveys.responses', ['slug' => $module->slug, 'type' => $type]) : null,
        ];
    }

    private function module(string $slug): Module
    {
        $course = Course::active()->firstOrFail();

        return Module::active()->where('course_id', $course->id)->where('slug', $slug)->firstOrFail();
    }
}
