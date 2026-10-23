<?php

use App\Http\Controllers\Auth\WebLoginController;
use App\Http\Controllers\Website\HomeController;
use App\Http\Controllers\Website\ModuleController;
use App\Http\Controllers\Website\ProfileController;
use App\Http\Middleware\EnsureMicrositeAccessIsOpen;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::middleware(EnsureMicrositeAccessIsOpen::class)->group(function () {
    Route::get('/login', [WebLoginController::class, 'login'])->name('login');
    Route::post('/login', [WebLoginController::class, 'loginPost'])->name('login.post');
    Route::get('/dashboard', [WebLoginController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard/agenda', [WebLoginController::class, 'agenda'])->name('dashboard.agenda');
    Route::get('/dashboard/cases', [WebLoginController::class, 'cases'])->name('dashboard.cases');
    Route::get('/dashboard/speakers', [WebLoginController::class, 'speakers'])->name('dashboard.speakers');
    Route::get('/dashboard/live', [WebLoginController::class, 'live'])->name('dashboard.live');
    Route::get('/dashboard/post-event-hub', [WebLoginController::class, 'hub'])->name('dashboard.hub');
    Route::get('/dashboard/takeaways', [WebLoginController::class, 'takeaways'])->name('dashboard.takeaways');
    Route::get('/dashboard/certificate', [WebLoginController::class, 'dashboardCertificate'])->name('dashboard.certificate');
    Route::get('/dashboard/profile', [WebLoginController::class, 'profile'])->name('dashboard.profile');
    Route::get('/course-dashboard', [WebLoginController::class, 'courseDashboard'])->name('course.dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/modules', [ModuleController::class, 'index'])->name('modules');
    Route::get('/modules/{slug}', [ModuleController::class, 'show'])->name('modules.show');
    Route::post('/modules/{slug}/comments', [ModuleController::class, 'storeComment'])->name('modules.comments.store');
    Route::post('/modules/{slug}/complete-video', [ModuleController::class, 'completeVideo'])->name('modules.complete_video');
    Route::post('/modules/{slug}/view-heartbeat', [ModuleController::class, 'trackViewHeartbeat'])->name('modules.view_heartbeat');
    Route::get('/modules/{slug}/quiz', [ModuleController::class, 'quiz'])->name('modules.quiz');
    Route::post('/modules/{slug}/quiz', [ModuleController::class, 'submitQuiz'])->name('modules.quiz.submit');
    Route::get('/modules/{slug}/certificate', [ModuleController::class, 'certificate'])->name('modules.certificate');
});
Route::post('/check-email', [HomeController::class, 'checkEmail'])->name('check_email');
Route::post('/check-mobile', [HomeController::class, 'checkMobile'])->name('check_mobile');
Route::get('/register', [WebLoginController::class, 'register'])->name('register');
Route::post('/register', [WebLoginController::class, 'registerPost'])->name('register.store');
Route::get('/logout', [HomeController::class, 'logout'])->name('logout');
