<?php

namespace App\Http\Controllers\Admin;

use App\Models\Module;
use App\Models\ModuleUserProgress;
use App\Models\ModuleViewSession;
use App\Models\Speciality;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'country' => ['nullable', 'string', 'max:255'],
            'module' => ['nullable', 'integer', 'exists:modules,id'],
            'period' => ['nullable', 'in:all,30,90,365'],
        ]);
        $filters = array_merge(['country' => '', 'module' => '', 'period' => 'all'], $filters);
        $since = $filters['period'] === 'all' ? null : now()->subDays((int) $filters['period'])->startOfDay();

        $userScope = function ($query) use ($filters, $since) {
            $query->where('users.type', 'doctor');
            if ($filters['country'] !== '') $query->where('users.country', $filters['country']);
            if ($since) $query->where('users.created_at', '>=', $since);
        };

        $participantQuery = User::query()->where($userScope);
        $participantCount = (clone $participantQuery)->count();

        $modules = Module::active()
            ->when($filters['module'], fn ($query) => $query->whereKey($filters['module']))
            ->with('course')
            ->orderBy('sort_order')->orderBy('id')->get();
        $moduleIds = $modules->pluck('id');

        $attendanceCount = ModuleViewSession::query()
            ->whereIn('module_id', $moduleIds)
            ->where('total_seconds', '>', 0)
            ->whereHas('user', $userScope)
            ->distinct('user_id')
            ->count('user_id');

        $progressQuery = ModuleUserProgress::query()
            ->whereIn('module_id', $moduleIds)
            ->whereNotNull('quiz_completed_at')
            ->whereHas('user', $userScope);
        $completedChapters = (clone $progressQuery)->count();
        $completedUserCounts = (clone $progressQuery)
            ->selectRaw('module_id, COUNT(DISTINCT user_id) as completed_users')
            ->groupBy('module_id')
            ->pluck('completed_users', 'module_id');

        $chapterProgress = $modules->map(function ($module) use ($completedUserCounts, $participantCount) {
            $completed = (int) ($completedUserCounts[$module->id] ?? 0);
            return [
                'id' => $module->id,
                'title' => $module->title,
                'course' => $module->course?->title,
                'completed' => $completed,
                'rate' => $participantCount ? min(100, (int) round($completed / $participantCount * 100)) : 0,
            ];
        });

        $advancedCount = 0;
        if ($moduleIds->isNotEmpty()) {
            $advancedCount = (clone $progressQuery)
                ->select('user_id')
                ->groupBy('user_id')
                ->havingRaw('COUNT(DISTINCT module_id) >= ?', [$moduleIds->count()])
                ->get()->count();
        }

        $specialityBreakdown = collect();
        $unspecifiedSpeciality = 0;
        if (Schema::hasColumn('users', 'speciality_id')) {
            $specialityBreakdown = Speciality::query()
                ->leftJoin('users', 'users.speciality_id', '=', 'specialities.id')
                ->where(function ($query) use ($userScope) {
                    $query->whereNull('users.id')->orWhere(function ($users) use ($userScope) {
                        $userScope($users);
                    });
                })
                ->selectRaw('specialities.name as label, COUNT(users.id) as total')
                ->groupBy('specialities.id', 'specialities.name')
                ->havingRaw('COUNT(users.id) > 0')
                ->orderByDesc('total')
                ->get();

            $unspecifiedSpeciality = (clone $participantQuery)->whereNull('speciality_id')->count();
        }
        if ($unspecifiedSpeciality) {
            $specialityBreakdown->push((object) ['label' => 'Not specified', 'total' => $unspecifiedSpeciality]);
        }

        $countryBreakdown = (clone $participantQuery)
            ->selectRaw("COALESCE(NULLIF(country, ''), 'Not specified') as label, COUNT(*) as total")
            ->groupBy('country')
            ->orderByDesc('total')
            ->take(12)
            ->get();

        $periodCutoffs = [
            '30' => now()->subDays(30)->startOfDay(),
            '90' => now()->subDays(90)->startOfDay(),
            '365' => now()->subDays(365)->startOfDay(),
        ];

        return view('admin.dashboard', [
            'title' => __('Dashboard'),
            'breadcrumb' => breadcrumb([__('Dashboard') => route('admin.dashboard')]),
            'filters' => $filters,
            'stats' => [
                'participants' => $participantCount,
                'attendance' => $attendanceCount,
                'completed_chapters' => $completedChapters,
                'participation_certificates' => $completedChapters,
                'advanced_certificates' => $advancedCount,
            ],
            'modules' => $modules,
            'chapterProgress' => $chapterProgress,
            'specialityBreakdown' => $specialityBreakdown,
            'countryBreakdown' => $countryBreakdown,
            'countries' => User::where('type', 'doctor')->whereNotNull('country')->where('country', '!=', '')->distinct()->orderBy('country')->pluck('country'),
            'allModules' => Module::active()->orderBy('sort_order')->orderBy('id')->get(['id', 'title']),
            'periodCutoffs' => $periodCutoffs,
        ]);
    }
}
