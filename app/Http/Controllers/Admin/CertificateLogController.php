<?php

namespace App\Http\Controllers\Admin;

use App\Models\Module;
use App\Models\ModuleUserProgress;
use App\Support\ExcelExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CertificateLogController extends Controller
{
    public function index()
    {
        return view('admin.certificate_logs.index', [
            'title' => __('Certificate Logs'),
            'modules' => Module::orderBy('sort_order')->orderBy('title')->get(),
            'breadcrumb' => breadcrumb([__('Certificate Logs') => route('admin.certificate_logs')]),
        ]);
    }

    public function datatable(Request $request)
    {
        $query = $this->baseQuery($request);
        $recordsTotal = DB::query()->fromSub(clone $query, 'certificate_rows')->count();

        $rows = $query
            ->orderByDesc('issued_at')
            ->skip((int) $request->start)
            ->take((int) $request->length)
            ->get()
            ->map(fn($row) => $this->formatRow($row));

        return response()->json([
            'draw' => (int) $request->draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsTotal,
            'data' => $rows,
        ]);
    }

    public function export(Request $request)
    {
        $rows = $this->baseQuery($request)
            ->orderByDesc('issued_at')
            ->get()
            ->map(function ($row, $index) {
                $formatted = $this->formatRow($row);

                return [
                    $index + 1,
                    $formatted['user_name'],
                    $formatted['email'],
                    $formatted['mobile'],
                    $formatted['module_title'],
                    'Participation',
                    $formatted['issued_at'],
                    $formatted['download_count'],
                    $formatted['first_downloaded_at'],
                    $formatted['latest_downloaded_at'],
                    $formatted['status'],
                ];
            });

        return ExcelExport::download(
            'certificate-logs-' . now()->format('Y-m-d-His'),
            ['No.', 'Doctor', 'Email', 'Mobile', 'Chapter', 'Certificate Type', 'Issued At', 'Download Count', 'First Download', 'Latest Download', 'Status'],
            $rows,
            'Certificate Logs'
        );
    }

    private function baseQuery(Request $request)
    {
        return ModuleUserProgress::query()
            ->join('modules', 'modules.id', '=', 'module_user_progress.module_id')
            ->join('users', 'users.id', '=', 'module_user_progress.user_id')
            ->leftJoin('certificate_download_logs', function ($join) {
                $join->on('certificate_download_logs.module_id', '=', 'module_user_progress.module_id')
                    ->on('certificate_download_logs.user_id', '=', 'module_user_progress.user_id');
            })
            ->whereNotNull('module_user_progress.video_completed_at')
            ->when($request->module_id, fn($query, $moduleId) => $query->where('modules.id', $moduleId))
            ->when($request->status === 'downloaded', fn($query) => $query->havingRaw('COUNT(certificate_download_logs.id) > 0'))
            ->when($request->status === 'available', fn($query) => $query->havingRaw('COUNT(certificate_download_logs.id) = 0'))
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('users.name', 'LIKE', "%$search%")
                        ->orWhere('users.email', 'LIKE', "%$search%")
                        ->orWhere('users.mobile', 'LIKE', "%$search%")
                        ->orWhere('modules.title', 'LIKE', "%$search%");
                });
            })
            ->select([
                'module_user_progress.id',
                'users.name as user_name',
                'users.email',
                'users.mobile',
                'modules.title as module_title',
                'module_user_progress.video_completed_at as issued_at',
                DB::raw('COUNT(certificate_download_logs.id) as download_count'),
                DB::raw('MIN(certificate_download_logs.downloaded_at) as first_downloaded_at'),
                DB::raw('MAX(certificate_download_logs.downloaded_at) as latest_downloaded_at'),
            ])
            ->groupBy(
                'module_user_progress.id',
                'users.name',
                'users.email',
                'users.mobile',
                'modules.title',
                'module_user_progress.video_completed_at'
            );
    }

    private function formatRow($row): array
    {
        $downloadCount = (int) $row->download_count;

        return [
            'id' => $row->id,
            'user_name' => $row->user_name ?: 'Delegate',
            'email' => $row->email ?: '-',
            'mobile' => $row->mobile ?: '-',
            'module_title' => $row->module_title ?: '-',
            'certificate_type' => 'Participation',
            'issued_at' => $this->formatDate($row->issued_at),
            'download_count' => $downloadCount,
            'first_downloaded_at' => $this->formatDate($row->first_downloaded_at),
            'latest_downloaded_at' => $this->formatDate($row->latest_downloaded_at),
            'status' => $downloadCount > 0 ? 'Downloaded' : 'Available',
        ];
    }

    private function formatDate($value): string
    {
        return $value ? Carbon::parse($value)->format('d M Y, h:i A') : '-';
    }
}
