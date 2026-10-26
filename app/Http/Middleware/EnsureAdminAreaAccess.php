<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAreaAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('admin')->user();

        if (!$user || $user->status !== 'active') {
            Auth::guard('admin')->logout();
            return redirect()->route('admin.login')->withErrors(['email' => 'This account is inactive.']);
        }

        if ($user->type === 'admin') {
            return $next($request);
        }

        if ($user->type !== 'employee' || $user->role !== 'sub_admin') {
            Auth::guard('admin')->logout();
            abort(403, 'You are not authorised to access the admin panel.');
        }

        $allowedRoutes = [
            'admin.dashboard',
            'admin.doctors',
            'admin.doctors.datatable',
            'admin.doctors.export',
            'admin.quizzes.submissions',
            'admin.quizzes.submissions.datatable',
            'admin.quizzes.submissions.export',
            'admin.quizzes.submissions.show',
            'admin.comments',
            'admin.comments.datatable',
            'admin.comments.export',
            'admin.reports.module_view_time',
            'admin.reports.module_view_time.datatable',
            'admin.reports.module_view_time.download',
            'admin.reports.module_view_time.download_attendees',
            'admin.reports.module_view_time.download_users',
            'admin.certificate_logs',
            'admin.certificate_logs.datatable',
            'admin.certificate_logs.export',
            'admin.live_questions.logs',
            'admin.live_questions.logs.datatable',
            'admin.live_questions.logs.export',
            'admin.live_questions',
            'admin.live_questions.analytics',
            'admin.live_questions.responses.export',
            'admin.profile',
            'admin.profile.update',
            'admin.password',
            'admin.password.update',
            'admin.check-email-exists',
            'admin.check-mobile-exists',
            'admin.logout',
        ];

        abort_unless($request->routeIs(...$allowedRoutes), 403, 'This section is not available for your account.');

        return $next($request);
    }
}
