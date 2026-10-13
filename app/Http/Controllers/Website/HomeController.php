<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\MyController;
use App\Models\Course;
use App\Models\Country;
use App\Models\Module;
use App\Models\QuizQuestion;
use App\Models\Speciality;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends MyController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $activeCourse = Course::active()->first();
        $modules = $activeCourse
            ? Module::active()->where('course_id', $activeCourse->id)->orderBy('sort_order')->orderBy('id')->get()
            : collect();

        $nearestModule = $modules
            ->filter(fn($module) => $module->end_at && $module->end_at->gte(now()))
            ->sortBy(fn($module) => $module->start_at?->timestamp ?? PHP_INT_MAX)
            ->first();

        $firstModuleDate = $modules->whereNotNull('start_at')->sortBy('start_at')->first()?->start_at;
        $lastModuleDate = $modules->whereNotNull('end_at')->sortByDesc('end_at')->first()?->end_at;
        $programMonths = ($firstModuleDate && $lastModuleDate)
            ? max(1, $firstModuleDate->diffInMonths($lastModuleDate) + 1)
            : 0;
        $response = [
            'title' => 'Home',
            'activeCourse' => $activeCourse,
            'modules' => $modules,
            'modulesCount' => $modules->count(),
            'programMonths' => $programMonths,
            'quizQuestionsCount' => QuizQuestion::active()->count(),
            'facultyCount' => $modules->pluck('doctor_id')->filter()->unique()->count(),
            'nearestModule' => $nearestModule,
            'countries' => Country::orderBy('name')->get(),
            'specialities' => Speciality::active()->orderBy('name')->get(),
        ];
        return view('website.home', $response);
    }

    public function checkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        return response()->json(!User::where('email', $request->email)->exists());
    }

    public function checkMobile(Request $request)
    {
        $request->validate([
            'mobile' => ['nullable', 'regex:/^\+[0-9]{7,20}$/'],
        ]);

        if (!$request->filled('mobile')) {
            return response()->json(true);
        }

        return response()->json(!User::where('mobile', $request->mobile)->exists());
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Logout successful');
    }

}
