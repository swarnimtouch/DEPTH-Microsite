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

        $questions = $module->surveyQuestions()->active()->where('survey_type', $type)->with(['options', 'rows'])->get();
        abort_if($questions->isEmpty(), 422, 'No active questions are available.');
        $validated = $request->validate(['answers' => ['required', 'array']]);

        foreach ($questions as $question) {
            $answer = $validated['answers'][$question->id] ?? null;
            if ($question->question_type === 'matrix_single_choice') {
                abort_unless(is_array($answer) && $question->rows->isNotEmpty(), 422, 'Please answer every row in the table question.');
                foreach ($question->rows as $row) {
                    $optionId = $answer[$row->id] ?? null;
                    abort_unless($question->options->contains('id', (int) $optionId), 422, 'Please answer every row in the table question.');
                }
            } else {
                abort_unless($question->options->contains('id', (int) $answer), 422, 'Please answer every survey question.');
            }
        }

        [$submission, $alreadySubmitted] = DB::transaction(function () use ($module, $questions, $validated, $type) {
            $submission = SurveySubmission::firstOrCreate(
                ['module_id' => $module->id, 'user_id' => Auth::guard('web')->id(), 'survey_type' => $type],
                ['submitted_at' => now()]
            );

            if (! $submission->wasRecentlyCreated) {
                $submission->load('answers');
                if (static::submissionCoversQuestions($submission, $questions)) {
                    return [$submission, true];
                }
            }

            $submission->answers()->whereIn('survey_question_id', $questions->pluck('id'))->delete();
            $answerOrder = ((int) $submission->answers()->max('sort_order')) + 1;
            foreach ($questions as $question) {
                if ($question->question_type === 'matrix_single_choice') {
                    foreach ($question->rows as $row) {
                        $option = $question->options->firstWhere('id', (int) $validated['answers'][$question->id][$row->id]);
                        $submission->answers()->create([
                            'survey_question_id' => $question->id,
                            'survey_question_row_id' => $row->id,
                            'survey_option_id' => $option->id,
                            'question_text' => $question->question,
                            'row_text' => $row->row_text,
                            'answer_text' => $option->option_text,
                            'sort_order' => $answerOrder++,
                        ]);
                    }
                } else {
                    $option = $question->options->firstWhere('id', (int) $validated['answers'][$question->id]);
                    $submission->answers()->create([
                        'survey_question_id' => $question->id,
                        'survey_option_id' => $option->id,
                        'question_text' => $question->question,
                        'answer_text' => $option->option_text,
                        'sort_order' => $answerOrder++,
                    ]);
                }
            }

            $submission->update(['submitted_at' => now()]);

            return [$submission, false];
        });

        return response()->json([
            'success' => true,
            'already_submitted' => $alreadySubmitted,
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
        $submission = SurveySubmission::with(['answers.question.options', 'answers.row'])
            ->where('module_id', $module->id)
            ->where('user_id', Auth::guard('web')->id())
            ->where('survey_type', $type)
            ->firstOrFail();

        $questions = $module->surveyQuestions()->active()->where('survey_type', $type)->with('rows')->get();
        abort_unless(static::submissionCoversQuestions($submission, $questions), 404);

        return view('website.modules.survey_responses', [
            'title' => ucfirst($type).'-event Survey Responses',
            'module' => $module,
            'submission' => $submission,
            'responses' => static::submissionResponses($submission, $questions->pluck('id')),
            'surveyType' => $type,
        ]);
    }

    private static function surveyState(Module $module, int $userId, string $type, bool $eligible): array
    {
        $activeQuestions = $module->surveyQuestions()->active()->where('survey_type', $type)->with(['options', 'rows'])->get();
        $enabled = $activeQuestions->isNotEmpty();
        $submission = SurveySubmission::with(['answers.question.options', 'answers.row'])
            ->where('module_id', $module->id)->where('user_id', $userId)->where('survey_type', $type)->first();
        $completed = $submission && static::submissionCoversQuestions($submission, $activeQuestions);
        $questions = $enabled && ! $completed ? $activeQuestions : collect();

        return [
            'enabled' => $enabled,
            'eligible' => $eligible,
            'completed' => (bool) $completed,
            'submitted_at' => $completed ? $submission?->submitted_at?->format('d M Y, h:i A') : null,
            'questions' => $questions->map(fn ($question) => [
                'id' => $question->id,
                'question' => $question->question,
                'type' => $question->question_type,
                'rows' => $question->rows->map(fn ($row) => ['id' => $row->id, 'text' => $row->row_text])->values(),
                'options' => $question->options->map(fn ($option) => ['id' => $option->id, 'text' => $option->option_text])->values(),
            ])->values(),
            'responses' => $completed ? static::submissionResponses($submission, $activeQuestions->pluck('id')) : [],
            'submit_url' => route('modules.surveys.submit', ['slug' => $module->slug, 'type' => $type]),
            'response_url' => $completed ? route('modules.surveys.responses', ['slug' => $module->slug, 'type' => $type]) : null,
        ];
    }

    private static function submissionCoversQuestions(SurveySubmission $submission, $questions): bool
    {
        if ($questions->isEmpty()) {
            return false;
        }

        return $questions->every(function ($question) use ($submission) {
            $answers = $submission->answers->where('survey_question_id', $question->id);
            if ($question->question_type === 'matrix_single_choice') {
                return $question->rows->isNotEmpty()
                    && $question->rows->every(fn ($row) => $answers->contains('survey_question_row_id', $row->id));
            }

            return $answers->isNotEmpty();
        });
    }

    private static function submissionResponses(SurveySubmission $submission, $questionIds = null): array
    {
        $answers = $submission->answers;
        if ($questionIds !== null) {
            $answers = $answers->whereIn('survey_question_id', $questionIds);
        }

        return $answers
            ->groupBy(fn ($answer) => $answer->survey_question_id ?: 'snapshot-'.$answer->question_text)
            ->map(function ($answers) {
                $first = $answers->first();
                $options = $first->question?->options?->pluck('option_text')->values() ?? collect();
                $isMatrix = $first->question?->question_type === 'matrix_single_choice' || $answers->contains(fn ($answer) => filled($answer->row_text));

                if ($isMatrix) {
                    return [
                        'question' => $first->question_text,
                        'type' => 'matrix_single_choice',
                        'options' => $options,
                        'rows' => $answers->map(function ($answer) use ($options) {
                            $rowOptions = $options->values();
                            if (! $rowOptions->contains($answer->answer_text)) {
                                $rowOptions->push($answer->answer_text);
                            }

                            return [
                                'row' => $answer->row_text ?: $answer->row?->row_text,
                                'answer' => $answer->answer_text,
                                'options' => $rowOptions,
                            ];
                        })->values(),
                    ];
                }

                if (! $options->contains($first->answer_text)) {
                    $options->push($first->answer_text);
                }

                return [
                    'question' => $first->question_text,
                    'type' => 'single_choice',
                    'answer' => $first->answer_text,
                    'options' => $options->values(),
                ];
            })->values()->all();
    }

    private function module(string $slug): Module
    {
        $course = Course::active()->firstOrFail();

        return Module::active()->where('course_id', $course->id)->where('slug', $slug)->firstOrFail();
    }
}
