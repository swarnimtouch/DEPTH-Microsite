<?php

namespace App\Http\Controllers\Admin;

use App\Models\Module;
use App\Models\ModuleViewSession;
use App\Models\QuizQuestion;
use App\Models\User;
use App\Support\ExcelExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModuleViewReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.module_view_time', [
            'title' => __('User Time Report'),
            'modules' => Module::orderBy('sort_order')->orderBy('title')->get(),
            'breadcrumb' => breadcrumb([__('User Time Report') => route('admin.reports.module_view_time')]),
        ]);
    }

    public function datatable(Request $request)
    {
        $query = $this->baseQuery($request);
        $recordsTotal = DB::query()->fromSub($query, 'report_rows')->count();
        $rows = $query
            ->orderBy('module_title')
            ->orderBy('user_name')
            ->skip((int) $request->start)
            ->take((int) $request->length)
            ->get()
            ->map(fn($row) => $this->formatRow($row));

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsTotal,
            'data' => $rows,
        ]);
    }

    public function download(Request $request)
    {
        $rows = $this->baseQuery($request)
            ->orderBy('module_title')
            ->orderBy('user_name')
            ->get();

        $exportRows = $rows->map(function ($row) {
            $formatted = $this->formatRow($row);
            return array_values($formatted);
        });

        return ExcelExport::download(
            'user-time-report-' . now()->format('Y-m-d-His'),
            ['Doctor', 'Email', 'Mobile', 'Chapter', 'Event Start', 'Event End', 'During Event Time', 'Post Event Time', 'Total Time', 'First Seen', 'Last Seen'],
            $exportRows
        );
    }

    public function downloadQuestions(Request $request)
    {
        $questions = QuizQuestion::with(['module', 'options'])
            ->when($request->module_id, fn($query) => $query->where('module_id', $request->module_id))
            ->orderBy('module_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $rows = $questions->map(fn($question) => [
            $question->module?->title ?: '-',
            $question->question,
            ($question->question_type ?? 'options') === 'text' ? 'Text' : 'Options',
            $question->options->pluck('option_text')->implode(' | '),
            $question->sort_order,
            ucfirst($question->status),
        ]);

        return ExcelExport::download(
            'session-wise-questions-' . now()->format('Y-m-d-His'),
            ['Session', 'Question', 'Question Type', 'Options', 'Order', 'Status'],
            $rows
        );
    }

    public function downloadAttendees(Request $request)
    {
        $rows = $this->baseQuery($request)
            ->orderBy('module_title')
            ->orderBy('user_name')
            ->get();

        $exportRows = $rows->map(function ($row) {
            $formatted = $this->formatRow($row);
            return [
                $formatted['module_title'], $formatted['user_name'], $formatted['email'], $formatted['mobile'],
                $formatted['event_start'], $formatted['event_end'], $formatted['first_seen'], $formatted['last_seen'],
                $formatted['during_event_time'], $formatted['post_event_time'], $formatted['total_time'],
            ];
        });

        return ExcelExport::download(
            'session-wise-attendees-' . now()->format('Y-m-d-His'),
            ['Session', 'Doctor', 'Email', 'Mobile', 'Event Start', 'Event End', 'First Seen', 'Last Seen', 'During Event Time', 'Post Event Time', 'Total Time'],
            $exportRows
        );
    }

    public function downloadUsers(Request $request)
    {
        $users = User::where('type', 'doctor')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%$search%")
                        ->orWhere('email', 'LIKE', "%$search%")
                        ->orWhere('mobile', 'LIKE', "%$search%")
                        ->orWhere('hospital', 'LIKE', "%$search%")
                        ->orWhere('country', 'LIKE', "%$search%");
                });
            })
            ->orderBy('name')
            ->get();

        $rows = $users->map(fn($user) => [
            $user->name ?: '-',
            $user->email ?: '-',
            $user->mobile ?: '-',
            $user->hospital ?: '-',
            $user->country ?: '-',
            ucfirst($user->status ?: '-'),
            $user->created_at?->format('d M Y, h:i A') ?: '-',
        ]);

        return ExcelExport::download(
            'all-users-' . now()->format('Y-m-d-His'),
            ['Doctor', 'Email', 'Mobile', 'Hospital', 'Country', 'Status', 'Registered At'],
            $rows
        );
    }

    private function baseQuery(Request $request)
    {
        return ModuleViewSession::query()
            ->join('modules', 'modules.id', '=', 'module_view_sessions.module_id')
            ->join('users', 'users.id', '=', 'module_view_sessions.user_id')
            ->when($request->module_id, fn($query) => $query->where('modules.id', $request->module_id))
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('users.name', 'LIKE', "%$search%")
                        ->orWhere('users.email', 'LIKE', "%$search%")
                        ->orWhere('users.mobile', 'LIKE', "%$search%")
                        ->orWhere('modules.title', 'LIKE', "%$search%");
                });
            })
            ->select([
                'users.name as user_name',
                'users.email',
                'users.mobile',
                'modules.title as module_title',
                'modules.start_at as event_start',
                'modules.end_at as event_end',
                DB::raw('MIN(module_view_sessions.started_at) as first_seen'),
                DB::raw('MAX(module_view_sessions.last_seen_at) as last_seen'),
                DB::raw('SUM(module_view_sessions.total_seconds) as total_seconds'),
                DB::raw('SUM(module_view_sessions.during_event_seconds) as during_event_seconds'),
                DB::raw('SUM(module_view_sessions.post_event_seconds) as post_event_seconds'),
            ])
            ->groupBy('users.id', 'users.name', 'users.email', 'users.mobile', 'modules.id', 'modules.title', 'modules.start_at', 'modules.end_at');
    }

    private function formatRow($row): array
    {
        return [
            'user_name' => $row->user_name ?: 'Delegate',
            'email' => $row->email ?: '-',
            'mobile' => $row->mobile ?: '-',
            'module_title' => $row->module_title,
            'event_start' => $this->formatDate($row->event_start),
            'event_end' => $this->formatDate($row->event_end),
            'during_event_time' => $this->formatDuration((int) $row->during_event_seconds),
            'post_event_time' => $this->formatDuration((int) $row->post_event_seconds),
            'total_time' => $this->formatDuration((int) $row->total_seconds),
            'first_seen' => $this->formatDate($row->first_seen),
            'last_seen' => $this->formatDate($row->last_seen),
        ];
    }

    private function formatDate($value): string
    {
        return $value ? \Carbon\Carbon::parse($value, 'UTC')->timezone(config('app.timezone'))->format('d M Y, h:i A') : '-';
    }

    private function formatDuration(int $seconds): string
    {
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $remainingSeconds = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $remainingSeconds);
    }
}
