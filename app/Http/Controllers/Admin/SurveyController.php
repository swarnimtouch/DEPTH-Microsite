<?php

namespace App\Http\Controllers\Admin;

use App\Events\SurveyAvailabilityChanged;
use App\Models\Module;
use App\Models\SurveyQuestion;
use App\Models\SurveySubmission;
use App\Support\ExcelExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SurveyController
{
    public function index(Request $request)
    {
        $questions = SurveyQuestion::with(['module', 'options', 'rows'])
            ->when($request->module_id, fn ($q, $id) => $q->where('module_id', $id))
            ->when(in_array($request->survey_type, ['pre', 'post'], true), fn ($q) => $q->where('survey_type', $request->survey_type))
            ->when($request->search, fn ($q, $search) => $q->where('question', 'LIKE', "%{$search}%"))
            ->orderBy('module_id')->orderBy('survey_type')->orderBy('sort_order')->paginate(20)->withQueryString();

        return view('admin.surveys.index', [
            'title' => 'Pre & Post Surveys', 'questions' => $questions,
            'modules' => Module::orderBy('sort_order')->orderBy('title')->get(),
            'breadcrumb' => breadcrumb(['Pre & Post Surveys' => '']),
        ]);
    }

    public function form(?int $id = null)
    {
        return view('admin.surveys.form', [
            'title' => 'Pre & Post Surveys',
            'question' => $id ? SurveyQuestion::with(['options', 'rows'])->findOrFail($id) : new SurveyQuestion(['survey_type' => 'pre', 'question_type' => 'single_choice', 'status' => 'active']),
            'modules' => Module::orderBy('sort_order')->orderBy('title')->get(),
            'breadcrumb' => breadcrumb(['Pre & Post Surveys' => route('admin.surveys'), $id ? 'Edit Question' : 'Add Question' => '']),
        ]);
    }

    public function save(Request $request, ?int $id = null)
    {
        $validated = $request->validate([
            'module_id' => ['required', Rule::exists('modules', 'id')],
            'survey_type' => ['required', Rule::in(['pre', 'post'])],
            'question_type' => ['required', Rule::in(['single_choice', 'matrix_single_choice'])],
            'question' => ['required', 'string', 'max:2000'],
            'options_text' => ['required', 'string'],
            'matrix_rows_text' => ['nullable', 'string', Rule::requiredIf($request->input('question_type') === 'matrix_single_choice')],
            'sort_order' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);
        $options = collect(preg_split('/\r\n|\r|\n/', $validated['options_text']))->map(fn ($v) => trim($v))->filter()->values();
        if ($options->count() < 2) {
            return back()->withInput()->withErrors(['options_text' => 'Please enter at least two options, one per line.']);
        }
        $rows = collect(preg_split('/\r\n|\r|\n/', (string) ($validated['matrix_rows_text'] ?? '')))->map(fn ($v) => trim($v))->filter()->values();
        if ($validated['question_type'] === 'matrix_single_choice' && $rows->count() < 2) {
            return back()->withInput()->withErrors(['matrix_rows_text' => 'Please enter at least two table rows, one per line.']);
        }

        $existing = $id ? SurveyQuestion::findOrFail($id) : null;
        $previousContext = $existing ? [$existing->module_id, $existing->survey_type] : null;

        $question = DB::transaction(function () use ($validated, $options, $rows, $existing) {
            $question = $existing ?: new SurveyQuestion;
            $question->fill(collect($validated)->except(['options_text', 'matrix_rows_text'])->all())->save();
            $question->options()->delete();
            foreach ($options as $index => $text) {
                $question->options()->create(['option_text' => $text, 'sort_order' => $index]);
            }
            $question->rows()->delete();
            if ($validated['question_type'] === 'matrix_single_choice') {
                foreach ($rows as $index => $text) {
                    $question->rows()->create(['row_text' => $text, 'sort_order' => $index]);
                }
            }

            return $question;
        });

        $this->broadcastAvailability($question->module_id, $question->survey_type);
        if ($previousContext && $previousContext !== [$question->module_id, $question->survey_type]) {
            $this->broadcastAvailability($previousContext[0], $previousContext[1]);
        }

        return redirect()->route('admin.surveys')->with('success', 'Survey question saved successfully.');
    }

    public function toggleQuestion(SurveyQuestion $surveyQuestion)
    {
        $surveyQuestion->update(['status' => $surveyQuestion->status === 'active' ? 'inactive' : 'active']);
        $this->broadcastAvailability($surveyQuestion->module_id, $surveyQuestion->survey_type);

        return response()->json(['success' => true, 'status' => $surveyQuestion->status]);
    }

    public function destroy(SurveyQuestion $surveyQuestion)
    {
        $moduleId = $surveyQuestion->module_id;
        $surveyType = $surveyQuestion->survey_type;
        $surveyQuestion->delete();
        $this->broadcastAvailability($moduleId, $surveyType);

        return response()->json(['success' => true]);
    }

    public function responses(Request $request)
    {
        $submissions = $this->responseQuery($request)->latest('submitted_at')->paginate(20)->withQueryString();

        return view('admin.surveys.responses', [
            'title' => 'Pre & Post Survey Responses', 'submissions' => $submissions,
            'modules' => Module::orderBy('sort_order')->orderBy('title')->get(),
            'breadcrumb' => breadcrumb(['Pre & Post Surveys' => route('admin.surveys'), 'Responses' => '']),
        ]);
    }

    public function export(Request $request)
    {
        $rows = $this->responseQuery($request)->latest('submitted_at')->get()->flatMap(function ($submission) {
            return $submission->answers->map(fn ($answer) => [
                $submission->user?->name ?: 'Delegate', $submission->user?->email ?: '-', $submission->user?->mobile ?: '-',
                $submission->module?->title ?: '-', ucfirst($submission->survey_type).'-event',
                $answer->row_text ? $answer->question_text.' — '.$answer->row_text : $answer->question_text,
                $answer->answer_text, $submission->submitted_at?->format('d M Y, h:i A'),
            ]);
        });

        return ExcelExport::download('survey-responses-'.now()->format('Y-m-d-His'),
            ['Doctor', 'Email', 'Mobile', 'Chapter', 'Survey', 'Question', 'Answer', 'Submitted At'], $rows, 'Survey Responses');
    }

    private function responseQuery(Request $request)
    {
        return SurveySubmission::with(['module', 'user', 'answers'])
            ->when($request->module_id, fn ($q, $id) => $q->where('module_id', $id))
            ->when(in_array($request->survey_type, ['pre', 'post'], true), fn ($q) => $q->where('survey_type', $request->survey_type))
            ->when($request->search, function ($q, $search) {
                $q->where(fn ($inner) => $inner->whereHas('user', fn ($u) => $u->where('name', 'LIKE', "%{$search}%")->orWhere('email', 'LIKE', "%{$search}%"))
                    ->orWhereHas('module', fn ($m) => $m->where('title', 'LIKE', "%{$search}%")));
            });
    }

    private function broadcastAvailability(int $moduleId, string $surveyType): void
    {
        $enabled = SurveyQuestion::active()
            ->where('module_id', $moduleId)
            ->where('survey_type', $surveyType)
            ->exists();

        try {
            event(new SurveyAvailabilityChanged($moduleId, $surveyType, $enabled));
        } catch (\Throwable $exception) {
            Log::warning('Survey Reverb broadcast failed.', [
                'module_id' => $moduleId,
                'survey_type' => $surveyType,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
