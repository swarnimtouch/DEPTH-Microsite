<?php

namespace App\Http\Controllers\Admin;

use App\Events\LiveQuestionChanged;
use App\Models\LiveQuestion;
use App\Models\LiveQuestionLog;
use App\Models\LiveQuestionResponse;
use App\Models\Module;
use App\Support\ExcelExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class LiveQuestionController extends Controller
{
    public function index()
    {
        return view('admin.live_questions.index', [
            'title' => 'Live Polls & Quizzes',
            'questions' => LiveQuestion::with('module')->withCount('responses')->latest()->paginate(20),
            'breadcrumb' => breadcrumb(['Live Polls & Quizzes' => '']),
        ]);
    }

    public function form(?int $id = null)
    {
        return view('admin.live_questions.form', [
            'title' => 'Live Polls & Quizzes',
            'question' => $id ? LiveQuestion::with('options')->findOrFail($id) : new LiveQuestion(['type' => 'poll']),
            'modules' => Module::orderBy('sort_order')->orderBy('title')->get(),
            'breadcrumb' => breadcrumb(['Live Polls & Quizzes' => route('admin.live_questions'), $id ? 'Edit' : 'Add Question' => '']),
        ]);
    }

    public function save(Request $request, ?int $id = null)
    {
        $validated = $request->validate([
            'module_id' => ['required', Rule::exists('modules', 'id')],
            'type' => ['required', Rule::in(['poll', 'quiz'])],
            'question' => ['required', 'string', 'max:2000'],
            'options' => ['required', 'array', 'min:2'],
            'options.*' => ['nullable', 'string', 'max:255'],
            'correct_option' => ['nullable', 'integer', 'min:0'],
        ]);

        $options = collect($validated['options'])->map(fn ($value) => trim((string) $value));
        $filledOptions = $options->filter(fn ($value) => $value !== '');
        if ($filledOptions->count() < 2) {
            return back()->withInput()->withErrors(['options' => 'Please enter at least two options.']);
        }
        if ($validated['type'] === 'quiz' && (!$request->filled('correct_option') || !$options->get((int) $validated['correct_option']))) {
            return back()->withInput()->withErrors(['correct_option' => 'Please select the correct quiz answer.']);
        }

        $question = DB::transaction(function () use ($validated, $filledOptions, $options, $id) {
            $question = $id ? LiveQuestion::findOrFail($id) : new LiveQuestion();
            abort_if($question->exists && $question->status === 'live', 422, 'Stop this question before editing it.');
            $question->fill([
                'module_id' => $validated['module_id'],
                'type' => $validated['type'],
                'question' => $validated['question'],
                'created_by' => $question->created_by ?: Auth::guard('admin')->id(),
            ])->save();
            $question->options()->delete();
            foreach ($options as $index => $text) {
                if ($text === '') continue;
                $question->options()->create([
                    'option_text' => $text,
                    'sort_order' => $index,
                    'is_correct' => $validated['type'] === 'quiz' && (int) $validated['correct_option'] === $index,
                ]);
            }
            LiveQuestionLog::create([
                'live_question_id' => $question->id,
                'admin_id' => Auth::guard('admin')->id(),
                'action' => $id ? 'updated' : 'created',
                'meta' => ['type' => $question->type],
            ]);
            return $question;
        });

        $this->broadcastChange($question->module_id, $id ? 'updated' : 'created', $question->id);
        return redirect()->route('admin.live_questions')->with('success', 'Live question saved successfully.');
    }

    public function toggle(LiveQuestion $liveQuestion)
    {
        $starting = $liveQuestion->status !== 'live';
        DB::transaction(function () use ($liveQuestion, $starting) {
            if ($starting) {
                $previouslyLive = LiveQuestion::where('module_id', $liveQuestion->module_id)
                    ->where('status', 'live')->whereKeyNot($liveQuestion->id)->get();
                foreach ($previouslyLive as $previous) {
                    $previous->update(['status' => 'stopped', 'stopped_at' => now()]);
                    LiveQuestionLog::create([
                        'live_question_id' => $previous->id,
                        'admin_id' => Auth::guard('admin')->id(),
                        'action' => 'stopped',
                        'meta' => ['reason' => 'Another question was started'],
                    ]);
                }
                $liveQuestion->update(['status' => 'live', 'started_at' => now(), 'stopped_at' => null]);
            } else {
                $liveQuestion->update(['status' => 'stopped', 'stopped_at' => now()]);
            }
            LiveQuestionLog::create([
                'live_question_id' => $liveQuestion->id,
                'admin_id' => Auth::guard('admin')->id(),
                'action' => $starting ? 'started' : 'stopped',
            ]);
        });
        $this->broadcastChange($liveQuestion->module_id, $starting ? 'started' : 'stopped', $liveQuestion->id);
        return response()->json(['success' => true, 'status' => $starting ? 'live' : 'stopped']);
    }

    public function destroy(LiveQuestion $liveQuestion)
    {
        abort_if($liveQuestion->status === 'live', 422, 'Stop this question before deleting it.');
        $moduleId = $liveQuestion->module_id;
        $questionId = $liveQuestion->id;
        DB::transaction(function () use ($liveQuestion) {
            LiveQuestionLog::create([
                'live_question_id' => $liveQuestion->id,
                'admin_id' => Auth::guard('admin')->id(),
                'action' => 'deleted',
                'meta' => ['question' => $liveQuestion->question, 'type' => $liveQuestion->type],
            ]);
            $liveQuestion->delete();
        });
        $this->broadcastChange($moduleId, 'deleted', $questionId);
        return response()->json(['success' => true]);
    }

    public function logs()
    {
        return view('admin.live_questions.logs', [
            'title' => 'Poll / Quiz Response Logs',
            'modules' => Module::orderBy('sort_order')->orderBy('title')->get(),
            'breadcrumb' => breadcrumb(['Live Polls & Quizzes' => route('admin.live_questions'), 'Logs' => '']),
        ]);
    }

    public function logsDatatable(Request $request)
    {
        $query = $this->responseLogQuery($request);
        $count = (clone $query)->count();
        $rows = $query->latest('answered_at')
            ->skip((int) $request->start)->take((int) $request->length)->get()
            ->map(fn (LiveQuestionResponse $response) => $this->formatResponseLog($response));

        return response()->json(['draw' => (int) $request->draw, 'recordsTotal' => $count, 'recordsFiltered' => $count, 'data' => $rows]);
    }

    public function exportLogs(Request $request)
    {
        $rows = $this->responseLogQuery($request)->latest('answered_at')->get()
            ->map(function (LiveQuestionResponse $response, int $index) {
                $row = $this->formatResponseLog($response);
                return [$index + 1, $row['user_name'], $row['email'], $row['mobile'], $row['module'], $row['type'], $row['question'], $row['answer'], $row['result'], $row['answered_at']];
            });

        return ExcelExport::download('poll-quiz-response-logs-' . now()->format('Y-m-d-His'),
            ['No.', 'Participant', 'Email', 'Mobile', 'Session', 'Type', 'Question', 'Answer', 'Result', 'Answered At'], $rows, 'Poll Quiz Responses');
    }

    private function responseLogQuery(Request $request)
    {
        return LiveQuestionResponse::with(['user', 'option', 'question.module'])
            ->when($request->module_id, fn ($query, $moduleId) => $query->whereHas('question', fn ($q) => $q->where('module_id', $moduleId)))
            ->when(in_array($request->type, ['poll', 'quiz'], true), fn ($query) => $query->whereHas('question', fn ($q) => $q->where('type', $request->type)))
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', fn ($user) => $user->where('name', 'LIKE', "%$search%")
                        ->orWhere('email', 'LIKE', "%$search%")->orWhere('mobile', 'LIKE', "%$search%"))
                        ->orWhereHas('question', fn ($question) => $question->where('question', 'LIKE', "%$search%")
                            ->orWhereHas('module', fn ($module) => $module->where('title', 'LIKE', "%$search%")))
                        ->orWhereHas('option', fn ($option) => $option->where('option_text', 'LIKE', "%$search%"));
                });
            });
    }

    private function formatResponseLog(LiveQuestionResponse $response): array
    {
        return [
            'id' => $response->id,
            'user_name' => $response->user?->name ?: 'Delegate',
            'email' => $response->user?->email ?: '-',
            'mobile' => $response->user?->mobile ?: '-',
            'module' => $response->question?->module?->title ?: '-',
            'type' => ucfirst($response->question?->type ?: '-'),
            'question' => $response->question?->question ?: '-',
            'answer' => $response->option?->option_text ?: '-',
            'result' => $response->question?->type === 'quiz' ? ($response->is_correct ? 'Correct' : 'Incorrect') : 'Vote',
            'answered_at' => $response->answered_at?->format('d M Y, h:i A') ?: '-',
        ];
    }

    private function broadcastChange(int $moduleId, string $action, ?int $questionId): void
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
