<?php

namespace App\Http\Controllers\Website;

use App\Events\LiveQuestionChanged;
use App\Models\LiveQuestion;
use App\Models\LiveQuestionLog;
use App\Models\LiveQuestionResponse;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class LiveQuestionController
{
    public function state(Module $module)
    {
        $userId = Auth::guard('web')->id();
        abort_unless($userId, 401, 'Please login first.');
        $question = LiveQuestion::with(['options' => fn ($query) => $query->withCount('responses')])
            ->where('module_id', $module->id)->where('status', 'live')->latest('started_at')->first();

        if (!$question) return response()->json(['question' => null]);

        $response = $question->responses()->where('user_id', $userId)->first();
        $total = $question->responses()->count();
        return response()->json(['question' => [
            'id' => $question->id,
            'type' => $question->type,
            'question' => $question->question,
            'answered' => (bool) $response,
            'selected_option_id' => $response?->live_question_option_id,
            'is_correct' => $response?->is_correct,
            'total_responses' => $total,
            'options' => $question->options->map(fn ($option) => [
                'id' => $option->id,
                'text' => $option->option_text,
                'responses' => $option->responses_count,
                'percent' => $total ? round(($option->responses_count / $total) * 100) : 0,
                'is_correct' => $response && $question->type === 'quiz' ? $option->is_correct : null,
            ])->values(),
        ]]);
    }

    public function answer(Request $request, LiveQuestion $liveQuestion)
    {
        abort_unless(Auth::guard('web')->check(), 401, 'Please login first.');
        abort_unless($liveQuestion->status === 'live', 422, 'This question is no longer live.');
        $validated = $request->validate([
            'option_id' => ['required', 'integer', Rule::exists('live_question_options', 'id')->where('live_question_id', $liveQuestion->id)],
        ]);
        $option = $liveQuestion->options()->findOrFail($validated['option_id']);
        $userId = Auth::guard('web')->id();

        DB::transaction(function () use ($liveQuestion, $option, $userId) {
            LiveQuestionResponse::firstOrCreate(
                ['live_question_id' => $liveQuestion->id, 'user_id' => $userId],
                ['live_question_option_id' => $option->id, 'is_correct' => $liveQuestion->type === 'quiz' ? $option->is_correct : null, 'answered_at' => now()]
            );
            LiveQuestionLog::firstOrCreate(
                ['live_question_id' => $liveQuestion->id, 'user_id' => $userId, 'action' => 'answered'],
                ['meta' => [
                    'option_id' => $option->id,
                    'answer' => $option->option_text,
                    'is_correct' => $liveQuestion->type === 'quiz' ? $option->is_correct : null,
                ]]
            );
        });

        $this->broadcastChange($liveQuestion->module_id, 'answered', $liveQuestion->id);
        return $this->state($liveQuestion->module);
    }

    private function broadcastChange(int $moduleId, string $action, int $questionId): void
    {
        try {
            event(new LiveQuestionChanged($moduleId, $action, $questionId));
        } catch (\Throwable $exception) {
            Log::warning('Live question Reverb broadcast failed.', [
                'module_id' => $moduleId,
                'question_id' => $questionId,
                'action' => $action,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
