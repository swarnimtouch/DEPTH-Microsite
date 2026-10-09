<?php

use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ImportDataController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\SiteSettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DivisionController;
use App\Http\Controllers\Admin\HeadquarterController;
use App\Http\Controllers\Admin\RegionsController;
use App\Http\Controllers\Admin\AreaController;
use App\Http\Controllers\Admin\SpecialitiesController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\DoctorController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\ModuleController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\ModuleViewReportController;
use App\Http\Controllers\Admin\WebinarsController;
use App\Http\Controllers\Admin\SessionViewUsersController;
use App\Http\Controllers\Admin\SessionCommentsController;
use App\Http\Controllers\Admin\FAQController;
use App\Http\Controllers\Admin\DatabaseServersController;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.login');
});
Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'authenticate'])->name('post_login');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth:admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('/user', UserController::class);
    Route::post('/user/delete-multiple', [UserController::class, 'deleteMultiple'])->name('user.deleteMultiple');
    Route::get('users/datatable', [UserController::class, 'datatable'])->name('user.datatable');
    Route::get('/my-profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/password', [ProfileController::class, 'password'])
        ->name('password');
    Route::post('check-email-exists', [ProfileController::class, 'checkEmailExists'])->name('check-email-exists');
    Route::post('check-mobile-exists', [ProfileController::class, 'checkMobileExists'])->name('check-mobile-exists');

    Route::post('/password/update', [ProfileController::class, 'updatePassword'])
        ->name('password.update');
    Route::get('site-settings', [SiteSettingsController::class, 'index'])->name('settings');
    Route::post('site-settings/update', [SiteSettingsController::class, 'update'])->name('settings.update');


    Route::get('content', [ContentController::class, 'index'])->name('content');
    Route::get('content/add-edit/{id?}', [ContentController::class, 'edit'])->name('content.add_edit_form');
    Route::put('content/update/{id}', [ContentController::class, 'update'])->name('content.update');
    Route::get('content/datatable', [ContentController::class, 'datatable'])->name('content.datatable');

    Route::prefix('faqs')->group(function () {
        Route::get('/', [FAQController::class, 'index'])->name('faqs');
        Route::get('/datatable', [FAQController::class, 'datatable'])->name('faqs.datatable');
        Route::get('/add-edit/{id?}', [FAQController::class, 'addEditForm'])->name('faqs.add_edit_form');
        Route::match(['POST', 'PUT'], '/save/{id?}', [FAQController::class, 'save'])->name('faqs.save');
        Route::post('/status-change/{id}', [FAQController::class, 'statusChange'])->name('faqs.status_change');
        Route::post('/delete/{id}', [FAQController::class, 'delete'])->name('faqs.delete');
        Route::post('/delete-multiple', [FAQController::class, 'deleteMultiple'])->name('faqs.delete_multiple');
    });

    Route::prefix('specialities')->group(function () {
        Route::get('/', [SpecialitiesController::class, 'index'])->name('specialities');
        Route::get('/datatable', [SpecialitiesController::class, 'datatable'])->name('specialities.datatable');
        Route::get('/add-edit/{id?}', [SpecialitiesController::class, 'addEditForm'])->name('specialities.add_edit_form');
        Route::match(['POST', 'PUT'], '/save/{id?}', [SpecialitiesController::class, 'save'])->name('specialities.save');
        Route::post('/status-change/{id}', [SpecialitiesController::class, 'statusChange'])->name('specialities.status_change');
        Route::post('/delete/{id}', [SpecialitiesController::class, 'delete'])->name('specialities.delete');
        Route::post('/delete-multiple', [SpecialitiesController::class, 'deleteMultiple'])->name('specialities.delete_multiple');
    });


    Route::prefix('employee')->group(function () {
        Route::get('/', [EmployeeController::class, 'index'])->name('employee');
        Route::get('/datatable', [EmployeeController::class, 'datatable'])->name('employee.datatable');
        Route::get('/add-edit/{id?}', [EmployeeController::class, 'addEditForm'])->name('employee.add_edit_form');
        Route::match(['POST', 'PUT'], '/save/{id?}', [EmployeeController::class, 'save'])->name('employee.save');
        Route::post('/status-change/{id}', [EmployeeController::class, 'statusChange'])->name('employee.status_change');
        Route::post('/delete/{id}', [EmployeeController::class, 'delete'])->name('employee.delete');
        Route::post('/delete-multiple', [EmployeeController::class, 'deleteMultiple'])->name('employee.delete_multiple');
    });

    Route::prefix('doctors')->group(function () {
        Route::get('/', [DoctorController::class, 'index'])->name('doctors');
        Route::get('/datatable', [DoctorController::class, 'datatable'])->name('doctors.datatable');
        Route::get('/export', [DoctorController::class, 'export'])->name('doctors.export');
        Route::get('/add-edit/{id?}', [DoctorController::class, 'addEditForm'])->name('doctors.add_edit_form');
        Route::match(['POST', 'PUT'], '/save/{id?}', [DoctorController::class, 'save'])->name('doctors.save');
        Route::post('/status-change/{id}', [DoctorController::class, 'statusChange'])->name('doctors.status_change');
        Route::post('/delete/{id}', [DoctorController::class, 'delete'])->name('doctors.delete');
        Route::post('/delete-multiple', [DoctorController::class, 'deleteMultiple'])->name('doctors.delete_multiple');
    });

    Route::prefix('courses')->group(function () {
        Route::get('/', [CourseController::class, 'index'])->name('courses');
        Route::get('/add-edit/{id?}', [CourseController::class, 'addEditForm'])->name('courses.add_edit_form');
        Route::match(['POST', 'PUT'], '/save/{id?}', [CourseController::class, 'save'])->name('courses.save');
        Route::post('/status-change/{id}', [CourseController::class, 'statusChange'])->name('courses.status_change');
        Route::post('/delete/{id}', [CourseController::class, 'delete'])->name('courses.delete');
        Route::post('/delete-multiple', [CourseController::class, 'deleteMultiple'])->name('courses.delete_multiple');
    });

    Route::prefix('modules')->group(function () {
        Route::get('/', [ModuleController::class, 'index'])->name('modules');
        Route::get('/datatable', [ModuleController::class, 'datatable'])->name('modules.datatable');
        Route::get('/certificate-template-preview/{id?}', [ModuleController::class, 'certificateTemplatePreview'])->name('modules.certificate_template_preview');
        Route::get('/add-edit/{id?}', [ModuleController::class, 'addEditForm'])->name('modules.add_edit_form');
        Route::match(['POST', 'PUT'], '/save/{id?}', [ModuleController::class, 'save'])->name('modules.save');
        Route::get('/{id}/agenda', [ModuleController::class, 'agendaForm'])->name('modules.agenda_form');
        Route::put('/{id}/agenda', [ModuleController::class, 'saveAgenda'])->name('modules.agenda_save');
        Route::post('/status-change/{id}', [ModuleController::class, 'statusChange'])->name('modules.status_change');
        Route::post('/delete/{id}', [ModuleController::class, 'delete'])->name('modules.delete');
        Route::post('/delete-multiple', [ModuleController::class, 'deleteMultiple'])->name('modules.delete_multiple');
    });

    Route::prefix('quizzes')->group(function () {
        Route::get('/', [QuizController::class, 'index'])->name('quizzes');
        Route::get('/datatable', [QuizController::class, 'datatable'])->name('quizzes.datatable');
        Route::get('/submissions', [QuizController::class, 'submissions'])->name('quizzes.submissions');
        Route::get('/submissions/datatable', [QuizController::class, 'submissionsDatatable'])->name('quizzes.submissions.datatable');
        Route::get('/submissions/{id}', [QuizController::class, 'showSubmission'])->name('quizzes.submissions.show');
        Route::post('/submissions/{id}/reply', [QuizController::class, 'saveSubmissionReply'])->name('quizzes.submissions.reply');
        Route::get('/add-edit/{id?}', [QuizController::class, 'addEditForm'])->name('quizzes.add_edit_form');
        Route::match(['POST', 'PUT'], '/save/{id?}', [QuizController::class, 'save'])->name('quizzes.save');
        Route::post('/status-change/{id}', [QuizController::class, 'statusChange'])->name('quizzes.status_change');
        Route::post('/delete/{id}', [QuizController::class, 'delete'])->name('quizzes.delete');
        Route::post('/delete-multiple', [QuizController::class, 'deleteMultiple'])->name('quizzes.delete_multiple');
    });

    Route::prefix('comments')->group(function () {
        Route::get('/', [CommentController::class, 'index'])->name('comments');
        Route::get('/datatable', [CommentController::class, 'datatable'])->name('comments.datatable');
        Route::post('/status-change/{id}', [CommentController::class, 'statusChange'])->name('comments.status_change');
        Route::post('/delete/{id}', [CommentController::class, 'delete'])->name('comments.delete');
    });

    Route::prefix('reports')->group(function () {
        Route::get('/module-view-time', [ModuleViewReportController::class, 'index'])->name('reports.module_view_time');
        Route::get('/module-view-time/datatable', [ModuleViewReportController::class, 'datatable'])->name('reports.module_view_time.datatable');
        Route::get('/module-view-time/download', [ModuleViewReportController::class, 'download'])->name('reports.module_view_time.download');
        Route::get('/module-view-time/download-questions', [ModuleViewReportController::class, 'downloadQuestions'])->name('reports.module_view_time.download_questions');
        Route::get('/module-view-time/download-attendees', [ModuleViewReportController::class, 'downloadAttendees'])->name('reports.module_view_time.download_attendees');
        Route::get('/module-view-time/download-users', [ModuleViewReportController::class, 'downloadUsers'])->name('reports.module_view_time.download_users');
    });

    Route::prefix('webinars')->group(function () {
        Route::get('/', [WebinarsController::class, 'index'])->name('webinars');
        Route::get('/datatable', [WebinarsController::class, 'datatable'])->name('webinars.datatable');
        Route::get('/add-edit/{id?}', [WebinarsController::class, 'addEditForm'])->name('webinars.add_edit_form');
        Route::match(['POST', 'PUT'], '/save/{id?}', [WebinarsController::class, 'save'])->name('webinars.save');
        Route::post('/status-change/{id}', [WebinarsController::class, 'statusChange'])->name('webinars.status_change');
        Route::post('/delete/{id}', [WebinarsController::class, 'delete'])->name('webinars.delete');
        Route::post('/delete-multiple', [WebinarsController::class, 'deleteMultiple'])->name('webinars.delete_multiple');
    });
});
