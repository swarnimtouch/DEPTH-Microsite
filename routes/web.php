<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Website\HomeController;
use App\Http\Controllers\Website\ModuleController;
use App\Http\Controllers\Website\ProfileController;
use App\Http\Controllers\Auth\WebLoginController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/login', [WebLoginController::class, 'login'])->name('login');
Route::post('/login', [WebLoginController::class, 'loginPost'])->name('login.post');
Route::get('/dashboard', [WebLoginController::class, 'dashboard'])->name('dashboard');
Route::get('/course-dashboard', [WebLoginController::class, 'courseDashboard'])->name('course.dashboard');
Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::get('/modules', [ModuleController::class, 'index'])->name('modules');
Route::get('/modules/{slug}', [ModuleController::class, 'show'])->name('modules.show');
Route::get('/modules/{slug}/comments', [ModuleController::class, 'comments'])->name('modules.comments');
Route::post('/modules/{slug}/comments', [ModuleController::class, 'storeComment'])->name('modules.comments.store');
Route::post('/modules/{slug}/comments/{comment}/upvote', [ModuleController::class, 'toggleCommentUpvote'])->name('modules.comments.upvote');
Route::post('/modules/{slug}/complete-video', [ModuleController::class, 'completeVideo'])->name('modules.complete_video');
Route::post('/modules/{slug}/view-heartbeat', [ModuleController::class, 'trackViewHeartbeat'])->name('modules.view_heartbeat');
Route::get('/modules/{slug}/quiz', [ModuleController::class, 'quiz'])->name('modules.quiz');
Route::post('/modules/{slug}/quiz', [ModuleController::class, 'submitQuiz'])->name('modules.quiz.submit');
Route::get('/modules/{slug}/certificate', [ModuleController::class, 'certificate'])->name('modules.certificate');
Route::post('/check-email', [HomeController::class, 'checkEmail'])->name('check_email');
Route::post('/check-mobile', [HomeController::class, 'checkMobile'])->name('check_mobile');
Route::get('/register', [WebLoginController::class, 'register'])->name('register');
Route::post('/register', [WebLoginController::class, 'registerPost'])->name('register.store');
Route::get('/logout', [HomeController::class, 'logout'])->name('logout');
