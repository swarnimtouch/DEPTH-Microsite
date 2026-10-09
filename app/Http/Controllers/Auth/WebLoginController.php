<?php

namespace App\Http\Controllers\Auth;

use App\Models\Country;
use App\Models\Course;
use App\Models\Module;
use App\Models\ModuleUserProgress;
use App\Models\Speciality;
use Illuminate\Validation\Rule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebLoginController
{
    public function login()
    {
        if (Auth::guard('web')->check()) {
            return redirect()->route('dashboard');
        }

        $response = [
            'title' => 'Login',
        ];
        return view('website.auth.login', $response);
    }

    public function dashboard()
    {
        if (!Auth::guard('web')->check()) {
            return redirect()
                ->route('login')
                ->with('error', 'Please login first');
        }

        return view('website.dashboard', $this->dashboardData('Dashboard'));
    }

    public function courseDashboard()
    {
        if (!Auth::guard('web')->check()) {
            return redirect()
                ->route('login')
                ->with('error', 'Please login first');
        }

        return view('website.course_dashboard', $this->dashboardData('Programme Dashboard'));
    }

    private function dashboardData(string $title): array
    {
        $doctor = Auth::guard('web')->user();
        $activeCourse = Course::active()->with(['activeModules.doctor'])->first();
        $modules = $activeCourse?->activeModules ?? collect();
        $progress = ModuleUserProgress::where('user_id', $doctor->id)
            ->whereIn('module_id', $modules->pluck('id'))
            ->get()
            ->keyBy('module_id');

        $completedModules = $progress->where('progress_percent', 100)->count();
        $totalModules = $modules->count();
        $quizProgress = $progress->whereNotNull('quiz_completed_at');
        $feedbackSubmitted = $quizProgress->count();

        $nearestModule = $modules
            ->filter(fn($module) => $module->end_at && $module->end_at->gte(now()))
            ->sortBy(fn($module) => $module->start_at?->timestamp ?? PHP_INT_MAX)
            ->first();

        $recentActivity = $progress
            ->flatMap(function ($item) {
                $items = collect();
                if ($item->quiz_completed_at) {
                    $items->push([
                        'title' => 'Submitted feedback: ' . $item->module->title,
                        'date' => $item->quiz_completed_at,
                    ]);
                }
                if ($item->video_completed_at) {
                    $items->push([
                        'title' => 'Completed video: ' . $item->module->title,
                        'date' => $item->video_completed_at,
                    ]);
                }
                return $items;
            })
            ->sortByDesc('date')
            ->take(3)
            ->values();

        $chartLabels = $quizProgress->map(function ($item) {
            return $item->module->title . ' (Chapter ' . ($item->module->sort_order) . ')';
        })->values();
        $chartScores = $quizProgress->map(fn($item) => $item->quiz_total ? round(($item->quiz_score / $item->quiz_total) * 100) : 0)->values();

        return [
            'title' => $title,
            'doctor' => $doctor,
            'activeCourse' => $activeCourse,
            'modules' => $modules,
            'progress' => $progress,
            'completedModules' => $completedModules,
            'totalModules' => $totalModules,
            'feedbackSubmitted' => $feedbackSubmitted,
            'nearestModule' => $nearestModule,
            'recentActivity' => $recentActivity,
            'chartLabels' => $chartLabels,
            'chartScores' => $chartScores,
        ];
    }

    public function register()
    {
        $response = [
            'title' => 'Register',
            'country' => Country::orderByRaw("CASE
                    WHEN name = 'India' THEN 0
                    WHEN name = 'Pakistan' THEN 1
                    WHEN name = 'Sri Lanka' THEN 2
                    ELSE 3
                END")
                ->orderBy('name')
                ->get(),
            'specialities' => Speciality::active()->orderBy('name')->get(),
        ];
        return view('website.auth.registration', $response);
    }

    public function registerPost(Request $request)
    {
        $isLandingRegistration = $request->input('registration_context') === 'landing';
        $validated = $request->validate([
            'registration_context' => ['nullable', Rule::in(['landing'])],
            'event' => [$isLandingRegistration ? 'required' : 'nullable', Rule::in(['1', '2', 'both'])],
            'consent' => [$isLandingRegistration ? 'accepted' : 'nullable'],
            'name' => ['required', 'string', 'max:255'],
            'hospital' => ['required', 'string', 'max:255'],
            'city' => [$isLandingRegistration ? 'required' : 'nullable', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255', Rule::exists('countries', 'name')],
            'mobile' => [$isLandingRegistration ? 'required' : 'nullable', 'regex:/^\+[0-9]{7,20}$/', 'unique:users,mobile'],
            'mobile_country_code' => ['nullable', 'string', 'max:5'],
            'mobile_country_iso' => ['nullable', 'string', 'size:2'],
            'mobile_number' => [$isLandingRegistration ? 'required' : 'nullable', 'digits_between:7,15'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'speciality_id' => ['required', Rule::exists('specialities', 'id')->where('status', 'active')],
        ]);

        $doctor = User::create([
            'type' => 'doctor',
            'status' => 'active',
            'name' => $validated['name'],
            'hospital' => $validated['hospital'],
            'city' => $validated['city'] ?? null,
            'country' => $validated['country'] ?? null,
            'mobile' => $validated['mobile'] ?? null,
            'email' => $validated['email'],
            'speciality_id' => $validated['speciality_id'],
        ]);

        Auth::guard('web')->login($doctor);
        $doctor->forceFill(['last_login_at' => now()])->save();
        $request->session()->regenerate();

        return redirect()
            ->route('modules')
            ->with('success', 'Doctor registered successfully');
    }

    public function loginPost(Request $request)
    {
        $validated = $request->validate([
            'registration_context' => ['nullable', Rule::in(['landing'])],
            'email' => ['required', 'email'],
        ]);

        $doctor = User::where('email', $validated['email'])
            ->where('type', 'doctor')
            ->where('status', 'active')
            ->first();

        if (!$doctor) {
            return redirect()
                ->route('login')
                ->withInput()
                ->with('error', 'No active doctor account found for this email address');
        }

        Auth::guard('web')->login($doctor);
        $doctor->forceFill(['last_login_at' => now()])->save();
        $request->session()->regenerate();

        return redirect()
            ->route('course.dashboard')
            ->with('success', 'Login successful');
    }
}
