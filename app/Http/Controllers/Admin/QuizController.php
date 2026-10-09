<?php

namespace App\Http\Controllers\Admin;

use App\Models\Module;
use App\Models\ModuleUserProgress;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QuizController
{
    public function index()
    {
        return view('admin.quizzes.index', [
            'title' => __('Feedback Questions'),
            'modules' => Module::orderBy('sort_order')->orderBy('title')->get(),
            'breadcrumb' => breadcrumb([__('Feedback Questions') => route('admin.quizzes')]),
        ]);
    }

    public function submissions()
    {
        return view('admin.quizzes.submissions', [
            'title' => __('Submitted Feedback'),
            'modules' => Module::orderBy('sort_order')->orderBy('title')->get(),
            'breadcrumb' => breadcrumb([__('Feedback Questions') => route('admin.quizzes'), __('Submitted Feedback') => route('admin.quizzes.submissions')]),
        ]);
    }

    public function submissionsDatatable(Request $request)
    {
        $query = ModuleUserProgress::with(['module', 'user'])
            ->whereNotNull('quiz_completed_at')
            ->whereNotNull('answers');

        if ($request->module_id) {
            $query->where('module_id', $request->module_id);
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('module', fn($module) => $module->where('title', 'LIKE', "%$search%"));
                $q->orWhereHas('user', fn($user) => $user
                    ->where('name', 'LIKE', "%$search%")
                    ->orWhere('email', 'LIKE', "%$search%")
                    ->orWhere('mobile', 'LIKE', "%$search%"));
            });
        }

        $recordsTotal = $query->count();
        $data = $query->latest('quiz_completed_at')
            ->skip($request->start)
            ->take($request->length)
            ->get()
            ->map(fn($progress) => [
                'id' => $progress->id,
                'module' => ['title' => $progress->module?->title ?: '-'],
                'user' => [
                    'name' => $progress->user?->name ?: 'Delegate',
                    'email' => $progress->user?->email ?: '-',
                    'mobile' => $progress->user?->mobile ?: '-',
                ],
                'quiz_completed_at' => $progress->quiz_completed_at?->format('d M Y, h:i A') ?: '-',
                'text_replies_count' => collect($progress->answers ?? [])->filter(fn($answer) => filled($answer['admin_reply'] ?? null))->count(),
            ]);

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsTotal,
            'data' => $data,
        ]);
    }

    public function showSubmission($id)
    {
        $progress = ModuleUserProgress::with(['module.questions.options', 'user'])
            ->whereNotNull('answers')
            ->findOrFail($id);

        return view('admin.quizzes.submission_show', [
            'title' => __('Submitted Feedback'),
            'progress' => $progress,
            'answers' => collect($progress->answers ?? []),
            'breadcrumb' => breadcrumb([
                __('Feedback Questions') => route('admin.quizzes'),
                __('Submitted Feedback') => route('admin.quizzes.submissions'),
                __('Details') => '',
            ]),
        ]);
    }

    public function saveSubmissionReply(Request $request, $id)
    {
        $progress = ModuleUserProgress::with('module.questions')
            ->whereNotNull('answers')
            ->findOrFail($id);

        $validated = $request->validate([
            'replies' => ['nullable', 'array'],
            'replies.*' => ['nullable', 'string', 'max:2000'],
        ]);

        $answers = $progress->answers ?? [];
        $textQuestionIds = $progress->module->questions
            ->where('question_type', 'text')
            ->pluck('id')
            ->map(fn($id) => (string) $id);

        foreach (($validated['replies'] ?? []) as $questionId => $reply) {
            $questionKey = (string) $questionId;

            if (!$textQuestionIds->contains($questionKey)) {
                continue;
            }

            if (!array_key_exists($questionKey, $answers) && !array_key_exists((int) $questionId, $answers)) {
                continue;
            }

            $answerKey = array_key_exists($questionKey, $answers) ? $questionKey : (int) $questionId;
            $answers[$answerKey]['admin_reply'] = trim((string) $reply);
            $answers[$answerKey]['admin_replied_at'] = trim((string) $reply) !== '' ? now()->toDateTimeString() : null;
        }

        $progress->answers = $answers;
        $progress->save();

        return back()->with('success', 'Reply saved successfully');
    }

    public function datatable(Request $request)
    {
        $query = QuizQuestion::with('module');

        if ($request->module_id) {
            $query->where('module_id', $request->module_id);
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('question', 'LIKE', "%$search%");
                $q->orWhereHas('module', fn($module) => $module->where('title', 'LIKE', "%$search%"));
            });
        }

        $recordsTotal = $query->count();
        $data = $query->orderBy('module_id')->orderBy('sort_order')->orderBy('id')
            ->skip($request->start)
            ->take($request->length)
            ->get();

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsTotal,
            'data' => $data,
        ]);
    }

    public function addEditForm($id = null)
    {
        $question = $id ? QuizQuestion::with('options')->findOrFail($id) : new QuizQuestion([
            'question_type' => 'options',
            'status' => 'active',
        ]);

        return view('admin.quizzes.add_edit', [
            'question' => $question,
            'modules' => Module::orderBy('sort_order')->orderBy('title')->get(),
            'title' => __('Feedback Questions'),
            'breadcrumb' => breadcrumb([__('Feedback Questions') => route('admin.quizzes'), ($id ? 'Edit' : 'Add Question') => '']),
        ]);
    }

    public function save(Request $request, $id = null)
    {
        $validated = $request->validate([
            'module_id' => ['required', Rule::exists('modules', 'id')],
            'question' => ['required', 'string'],
            'question_type' => ['required', Rule::in(['options', 'text'])],
            'sort_order' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'options' => ['nullable', 'array'],
            'options.*' => ['nullable', 'string', 'max:255'],
        ]);

        $options = collect($validated['options'] ?? [])
            ->map(fn($option) => trim((string) $option))
            ->filter()
            ->values();

        if ($validated['question_type'] === 'options' && $options->count() < 2) {
            return back()->withInput()->withErrors(['options' => 'Please add at least two feedback options.']);
        }

        $question = $id ? QuizQuestion::findOrFail($id) : new QuizQuestion();
        $question->fill([
            'module_id' => $validated['module_id'],
            'question' => $validated['question'],
            'question_type' => $validated['question_type'],
            'sort_order' => $validated['sort_order'],
            'status' => $validated['status'],
        ]);
        $question->save();

        $question->options()->delete();
        if ($validated['question_type'] === 'options') {
            foreach ($options as $index => $optionText) {
                $question->options()->create([
                    'option_text' => $optionText,
                    'description' => null,
                    'is_correct' => false,
                    'sort_order' => $index,
                ]);
            }
        }

        return redirect()->route('admin.quizzes')->with('success', 'Feedback question saved successfully');
    }

    public function statusChange($id)
    {
        $question = QuizQuestion::findOrFail($id);
        $question->status = $question->status === 'active' ? 'inactive' : 'active';
        $question->save();

        return response()->json(['success' => true]);
    }

    public function delete($id)
    {
        QuizQuestion::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    public function deleteMultiple(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:quiz_questions,id'],
        ]);

        QuizQuestion::whereIn('id', $validated['ids'])->delete();

        return response()->json(['success' => true, 'message' => 'Feedback questions deleted successfully']);
    }
}
