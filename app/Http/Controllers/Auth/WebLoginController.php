<?php

namespace App\Http\Controllers\Auth;

use App\Mail\RegistrationConfirmation;
use App\Models\Country;
use App\Models\Course;
use App\Models\Module;
use App\Models\ModuleUserProgress;
use App\Models\Speciality;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Throwable;

class WebLoginController
{
    public function login()
    {
        if (Auth::guard('web')->check()) {
            return redirect()->route('dashboard');
        }

        return redirect()
            ->to(route('home') . '#register')
            ->with('show_login', true);
    }

    public function dashboard()
    {
        return $this->dashboardSection('overview', 'Program overview');
    }

    public function agenda()
    {
        return $this->dashboardSection('agenda', 'Agenda');
    }

    public function cases()
    {
        return $this->dashboardSection('cases', 'Case booklets');
    }

    public function speakers()
    {
        return $this->dashboardSection('speakers', 'Speaker');
    }

    public function live()
    {
        return $this->dashboardSection('live', 'Live session');
    }

    public function hub()
    {
        return $this->dashboardSection('hub', 'Post-event hub');
    }

    public function takeaways()
    {
        return $this->dashboardSection('takeaways', 'Key takeaways');
    }

    public function dashboardCertificate()
    {
        return redirect()->route('dashboard.hub');
    }

    public function profile()
    {
        return $this->dashboardSection('profile', 'Profile');
    }

    private function dashboardSection(string $section, string $title)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()
                ->route('login')
                ->with('error', 'Please login first');
        }

        return view('website.depth_dashboard', [
            ...$this->dashboardData($title),
            'dashboardSection' => $section,
            'dashboardRoutes' => [
                'overview' => route('dashboard'),
                'agenda' => route('dashboard.agenda'),
                'cases' => route('dashboard.cases'),
                'speakers' => route('dashboard.speakers'),
                'live' => route('dashboard.live'),
                'hub' => route('dashboard.hub'),
                'takeaways' => route('dashboard.takeaways'),
                'profile' => route('dashboard.profile'),
            ],
        ]);
    }

    public function courseDashboard()
    {
        if (!Auth::guard('web')->check()) {
            return redirect()
                ->route('login')
                ->with('error', 'Please login first');
        }

        return redirect()->route('dashboard');
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
        $agendaModule = $nearestModule ?: $modules->first();
        $agendaModule?->loadMissing('resourceDocuments');
        $depthAgenda = collect($agendaModule?->agenda ?? [])->map(fn($row) => [
            'type' => $row['type'] ?? 'person',
            'time' => $row['time'] ?? null,
            'duration' => isset($row['duration']) && $row['duration'] !== '' ? (int) $row['duration'] : null,
            'title' => $row['title'] ?? '',
            'role' => $row['role'] ?? '',
            'names' => $row['names'] ?? ($row['name'] ?? ''),
        ])->filter(fn($row) => $row['title'] !== '' || $row['role'] !== '' || $row['names'] !== '')->values();
        $speakerProfiles = collect($agendaModule?->speakers ?? []);
        if ($speakerProfiles->isEmpty() && $agendaModule?->speaker_name) {
            $speakerProfiles = collect([[
                'name' => $agendaModule->speaker_name,
                'role' => 'Speaker',
                'current_position' => $agendaModule->speaker_designation,
                'designation' => $agendaModule->speaker_designation,
                'hospital' => $agendaModule->speaker_hospital,
                'country' => '',
                'city' => '',
                'bio' => '',
                'professional_profile' => '',
                'expertise' => '',
                'session_title' => $agendaModule->title,
                'profile_resource_url' => '',
                'image' => null,
            ]]);
        }
        $publicUrl = static fn (string $path): string => (request()->getBaseUrl() ?: '') . '/' . ltrim($path, '/');

        $depthSpeakers = $speakerProfiles->map(function ($speaker) use ($agendaModule, $publicUrl) {
            $expertise = is_array($speaker['expertise'] ?? null)
                ? $speaker['expertise']
                : array_values(array_filter(array_map('trim', explode(',', $speaker['expertise'] ?? ''))));
            $name = $speaker['name'] ?? 'Speaker';
            $designation = $speaker['designation'] ?? '';
            $hospital = $speaker['hospital'] ?? '';
            $country = $speaker['country'] ?? '';
            $city = $speaker['city'] ?? '';
            $image = $speaker['image'] ?? '';

            return [
                'name' => $name,
                'photo' => $image
                    ? (str_starts_with($image, 'web/') ? $publicUrl($image) : $publicUrl('storage/' . $image))
                    : $publicUrl('web/depth/assets/speakers/kasper.jpg'),
                'role' => $speaker['role'] ?? 'Speaker',
                'designation' => $designation,
                'designation2' => $hospital,
                'place' => implode(', ', array_filter([$city, $country])) ?: $hospital,
                'bio' => strip_tags($speaker['bio'] ?? '') ?: trim($designation . ($hospital ? ', ' . $hospital : '')),
                'expertise' => $expertise,
                'resourceUrl' => $speaker['profile_resource_url'] ?? '',
                'profile' => [
                    ['Current position', $speaker['current_position'] ?? ($designation ?: '—')],
                    ['Country / City', implode(', ', array_filter([$city, $country])) ?: '—'],
                    ['Institution / Hospital', $hospital ?: '—'],
                    ['Professional profile', strip_tags($speaker['professional_profile'] ?? '') ?: (strip_tags($speaker['bio'] ?? '') ?: '—')],
                    ['Clinical / research expertise', $expertise ? implode(' • ', $expertise) : '—'],
                    ['Role in webinar', $speaker['role'] ?? 'Speaker'],
                    ['Session', $speaker['session_title'] ?? ($agendaModule?->title ?? 'DEPTH')],
                ],
            ];
        })->values();

        $eventStart = $agendaModule?->getRawOriginal('start_at')
            ? CarbonImmutable::parse($agendaModule->getRawOriginal('start_at'), 'UTC')->setTimezone('Asia/Kolkata')
            : null;
        $eventEnd = $agendaModule?->getRawOriginal('end_at')
            ? CarbonImmutable::parse($agendaModule->getRawOriginal('end_at'), 'UTC')->setTimezone('Asia/Kolkata')
            : null;
        $moduleProgress = $agendaModule ? $progress->get($agendaModule->id) : null;
        $fullRecordingResource = $agendaModule?->resourceDocuments
            ?->firstWhere('category', 'full_recording');
        $fullRecordingReady = $fullRecordingResource
            && $fullRecordingResource->status === 'active'
            && ($fullRecordingResource->file || $fullRecordingResource->external_url)
            && (!$fullRecordingResource->getRawOriginal('available_at')
                || CarbonImmutable::parse($fullRecordingResource->getRawOriginal('available_at'), 'UTC')->lte(now('UTC')));
        $depthEvent = $agendaModule ? [
            'title' => $agendaModule->title,
            'full' => $agendaModule->title,
            'tagline' => trim(strip_tags((string) $agendaModule->overview)),
            'desc' => trim(strip_tags((string) $agendaModule->overview)),
            'date' => $eventStart?->format('d F Y'),
            'dateShort' => $eventStart?->format('d M Y'),
            'day' => $eventStart?->format('l'),
            'time' => $eventStart && $eventEnd
                ? $eventStart->format('g:i A') . ' – ' . $eventEnd->format('g:i A') . ' IST'
                : ($agendaModule->duration ?: 'Time to be announced'),
            'start' => $agendaModule->start_at_utc_iso,
            'end' => $agendaModule->end_at_utc_iso,
            'duration' => $agendaModule->duration,
            'joinEnabled' => (bool) $agendaModule->join_enabled,
            'videoPageUrl' => route('modules.show', $agendaModule->slug),
            'recordingAvailable' => (bool) ($agendaModule->video_url || $fullRecordingReady),
            'roomUrl' => $agendaModule->join_url ?: route('modules.show', $agendaModule->slug),
            'certificateUrl' => route('modules.certificate', $agendaModule->slug),
            'certificateUnlocked' => (bool) $moduleProgress?->video_completed_at,
        ] : [];

        $caseFallbackImages = [
            'case1-ward.jpg',
            'case2-phq9.jpg',
            'case3-patient.jpg',
            'case4-pharma.jpg',
        ];
        $caseFallbacksById = collect($agendaModule?->resourceDocuments ?? [])
            ->where('category', 'case_book')
            ->values()
            ->mapWithKeys(function ($resource, $index) use ($caseFallbackImages, $publicUrl) {
                return [
                    $resource->id => $publicUrl('web/depth/assets/visual/' . $caseFallbackImages[$index % count($caseFallbackImages)]),
                ];
            });

        $depthResources = collect($agendaModule?->resourceDocuments ?? [])->map(function ($resource) use ($caseFallbacksById, $publicUrl) {
            $url = $resource->external_url ?: ($resource->file ? $publicUrl('storage/' . $resource->file) : null);
            $hasSource = (bool) $url;
            $extension = strtolower(pathinfo((string) (parse_url($url ?? '', PHP_URL_PATH) ?: $resource->file), PATHINFO_EXTENSION));
            $videoExtensions = ['mp4', 'webm', 'mov'];
            $embedUrl = Module::youtubeEmbedUrl($resource->external_url);
            if (!$embedUrl && $resource->resource_type === 'video' && $resource->external_url && !in_array($extension, $videoExtensions, true)) {
                $embedUrl = $resource->external_url;
            }

            return [
                'id' => $resource->id,
                'label' => $resource->label,
                'description' => $resource->description,
                'category' => $resource->category ?: 'general',
                'url' => $url,
                'embedUrl' => $embedUrl,
                'thumbnail' => $resource->thumbnail
                    ? $publicUrl('storage/' . $resource->thumbnail)
                    : $caseFallbacksById->get($resource->id),
                'type' => $resource->resource_type === 'video' || $embedUrl || in_array($extension, $videoExtensions, true) ? 'Video' : ($resource->resource_type === 'pdf' || $extension === 'pdf' ? 'PDF' : 'Link'),
                'isVideo' => (bool) ($resource->resource_type === 'video' || $embedUrl || in_array($extension, $videoExtensions, true)),
                'isPdf' => $resource->resource_type === 'pdf' || $extension === 'pdf',
                'available' => $hasSource && $resource->status === 'active' && (!$resource->getRawOriginal('available_at') || CarbonImmutable::parse($resource->getRawOriginal('available_at'), 'UTC')->lte(now('UTC'))),
                'availableAt' => $resource->getRawOriginal('available_at') ? CarbonImmutable::parse($resource->getRawOriginal('available_at'), 'UTC')->toIso8601String() : null,
            ];
        })->values();

        return [
            'title' => $title,
            'doctor' => $doctor,
            'countries' => Country::orderByRaw("CASE WHEN name = 'India' THEN 0 ELSE 1 END")
                ->orderBy('name')
                ->get(),
            'specialities' => Speciality::active()->orderBy('name')->get(),
            'depthUser' => [
                'name' => $doctor->name,
                'spec' => $doctor->speciality?->name,
                'inst' => $doctor->hospital,
                'country' => $doctor->country,
                'city' => $doctor->city,
                'email' => $doctor->email,
                'mobile' => $doctor->mobile,
                'event' => '1',
                'registered' => optional($doctor->created_at)->toIso8601String(),
            ],
            'depthAgenda' => $depthAgenda,
            'depthSpeakers' => $depthSpeakers,
            'depthEvent' => $depthEvent,
            'depthResources' => $depthResources,
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
            'event' => [
                $isLandingRegistration ? 'required' : 'nullable',
                Rule::exists('modules', 'id')->where(fn ($query) => $query->where('status', 'active')),
            ],
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

        if (config('mail.enabled')) {
            try {
                Mail::to($doctor->email)->send(new RegistrationConfirmation($doctor));
            } catch (Throwable $exception) {
                Log::error('Registration confirmation email could not be sent.', [
                    'user_id' => $doctor->id,
                    'recipient' => $doctor->email,
                    'exception' => $exception,
                ]);
            }
        }

        return redirect()
            ->route('dashboard')
            ->with('success', 'Doctor registered successfully');
    }

    public function loginPost(Request $request)
    {
        $validated = $request->validate([
            'login_context' => ['nullable', Rule::in(['landing'])],
            'email' => ['required', 'email'],
        ]);

        $isLandingLogin = ($validated['login_context'] ?? null) === 'landing';

        $doctor = User::where('email', $validated['email'])
            ->where('type', 'doctor')
            ->where('status', 'active')
            ->first();

        if (!$doctor) {
            $redirect = $isLandingLogin
                ? redirect()->to(route('home') . '#register')
                : redirect()->route('login');

            return $redirect
                ->withInput()
                ->with('show_login', $isLandingLogin)
                ->with('error', 'No active doctor account found for this email address');
        }

        Auth::guard('web')->login($doctor);
        $doctor->forceFill(['last_login_at' => now()])->save();
        $request->session()->regenerate();

        return redirect()
            ->route('dashboard')
            ->with('success', 'Login successful');
    }
}
