<?php

use Illuminate\Support\Facades\Route;

if (!function_exists('pre')) {
    function pre($data = null, $exit = true): void
    {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        if ($exit)
            exit;
    }
}

if (!function_exists('print_query')) {
    function print_query($query): string
    {
        return vsprintf(
            str_replace('?', '%s', $query->toSql()),
            collect($query->getBindings())->map(fn($b) => is_numeric($b) ? $b : "'$b'")->toArray()
        );
    }
}

if (!function_exists('breadcrumb')) {
    function breadcrumb(array $aBreadcrumb = []): string
    {
        $aBreadcrumb = array_merge(['Home' => route('admin.dashboard')], $aBreadcrumb);

        if (empty($aBreadcrumb)) {
            return '';
        }

        $html = '';
        $i = 1;
        $total = count($aBreadcrumb);

        foreach ($aBreadcrumb as $key => $link) {
            $label = htmlspecialchars(ucfirst($key), ENT_QUOTES, 'UTF-8');
            $url = $link !== ''
                ? htmlspecialchars($link, ENT_QUOTES, 'UTF-8')
                : 'javascript:void(0)';

            $html .= '<li class="breadcrumb-item text-muted">';
            $html .= '<a href="' . $url . '" class="text-muted text-hover-primary">';
            $html .= $label;
            $html .= '</a>';
            $html .= '</li>';

            if ($total > $i) {
                $html .= '<li class="breadcrumb-item">';
                $html .= '<span class="bullet bg-gray-200 w-5px h-2px"></span>';
                $html .= '</li>';
            }

            $i++;
        }

        return $html;
    }
}

function getModules($user_type = 'admin', ?string $role = null): array
{
    $currentRoute = Route::current();
    $module = [];
    if ($user_type == 'admin') {
        $module = [
            [
                'route' => route('admin.dashboard'),
                'name' => 'Dashboard',
                'icon' => 'bi bi-grid fs-3',
                'child' => [],
                'all_routes' => ['admin.dashboard']
            ],

            [
                'route' => route('admin.doctors'),
                'name' => 'Doctors',
                'icon' => 'bi bi-briefcase fs-3',
                'child' => [],
                'all_routes' => ['admin.doctors', 'admin.doctors.add_edit_form']
            ],
            [
                'route' => route('admin.sub_admins'),
                'name' => 'Sub Admins',
                'icon' => 'bi bi-person-badge fs-3',
                'child' => [],
                'all_routes' => ['admin.sub_admins', 'admin.sub_admins.form']
            ],
            [
                'route' => route('admin.modules'),
                'name' => 'Chapters',
                'icon' => 'bi bi-book fs-3',
                'child' => [],
                'all_routes' => ['admin.modules', 'admin.modules.add_edit_form']
            ],
            [
                'route' => route('admin.courses'),
                'name' => 'Programme',
                'icon' => 'bi bi-journal-richtext fs-3',
                'child' => [],
                'all_routes' => ['admin.courses', 'admin.courses.add_edit_form']
            ],
            [
                'route' => route('admin.quizzes'),
                'name' => 'Feedback Questions',
                'icon' => 'bi bi-ui-checks fs-3',
                'child' => [],
                'all_routes' => ['admin.quizzes', 'admin.quizzes.add_edit_form', 'admin.quizzes.submissions', 'admin.quizzes.submissions.show']
            ],
            [
                'route' => route('admin.quizzes.submissions'),
                'name' => 'Submitted Feedback',
                'icon' => 'bi bi-chat-square-text fs-3',
                'child' => [],
                'all_routes' => ['admin.quizzes.submissions', 'admin.quizzes.submissions.show']
            ],
            [
                'route' => route('admin.live_questions'),
                'name' => 'Live Polls & Quizzes',
                'icon' => 'bi bi-broadcast fs-3',
                'child' => [],
                'all_routes' => ['admin.live_questions', 'admin.live_questions.form']
            ],
            [
                'route' => route('admin.live_questions.logs'),
                'name' => 'Poll / Quiz Logs',
                'icon' => 'bi bi-list-check fs-3',
                'child' => [],
                'all_routes' => ['admin.live_questions.logs', 'admin.live_questions.logs.datatable', 'admin.live_questions.logs.export']
            ],
            [
                'route' => route('admin.comments'),
                'name' => 'Comments',
                'icon' => 'bi bi-chat-left-text fs-3',
                'child' => [],
                'all_routes' => ['admin.comments']
            ],
            [
                'route' => route('admin.reports.module_view_time'),
                'name' => 'Time Reports',
                'icon' => 'bi bi-clock-history fs-3',
                'child' => [],
                'all_routes' => ['admin.reports.module_view_time']
            ],
            [
                'route' => route('admin.certificate_logs'),
                'name' => 'Certificate Logs',
                'icon' => 'bi bi-award fs-3',
                'child' => [],
                'all_routes' => ['admin.certificate_logs'],
            ],
            [
                'route' => '#',
                'name' => 'General Settings',
                'icon' => 'bi bi-gear',
                'child' => [
                    [
                        'route' => route('admin.settings'),
                        'name' => 'Site Settings',
                        'icon' => 'bi bi-sliders',
                        'all_routes' => ['admin.settings']
                    ],
                    [
                        'route' => route('admin.content'),
                        'name' => 'Content',
                        'icon' => 'bi bi-file-text',
                        'all_routes' => ['admin.content', 'admin.content.add_edit_form']
                    ],
                    [
                        'route' => route('admin.faqs'),
                        'name' => 'FAQ',
                        'icon' => 'bi bi-question-circle',
                        'all_routes' => ['admin.faqs', 'admin.faqs.add_edit_form']
                    ]
                ],
                'all_routes' => [
                    'admin.settings', 'admin.banners', 'admin.banner.add_edit_form',
                    'admin.speakers', 'admin.speaker.add_edit_form',
                    'admin.brand', 'admin.brand.add_edit_form',
                    'admin.content', 'admin.content.add_edit_form',
                    'admin.faqs', 'admin.faqs.add_edit_form'
                ]
            ]
        ];
    } elseif ($user_type === 'employee' && $role === 'sub_admin') {
        $module = [
            [
                'route' => route('admin.dashboard'),
                'name' => 'Dashboard',
                'icon' => 'bi bi-grid fs-3',
                'child' => [],
                'all_routes' => ['admin.dashboard'],
            ],
            [
                'route' => route('admin.doctors'),
                'name' => 'Doctors',
                'icon' => 'bi bi-briefcase fs-3',
                'child' => [],
                'all_routes' => ['admin.doctors'],
            ],
            [
                'route' => route('admin.quizzes.submissions'),
                'name' => 'Submitted Feedback',
                'icon' => 'bi bi-chat-square-text fs-3',
                'child' => [],
                'all_routes' => ['admin.quizzes.submissions', 'admin.quizzes.submissions.show'],
            ],
            [
                'route' => route('admin.comments'),
                'name' => 'Comments',
                'icon' => 'bi bi-chat-left-text fs-3',
                'child' => [],
                'all_routes' => ['admin.comments'],
            ],
            [
                'route' => route('admin.reports.module_view_time'),
                'name' => 'Time Reports',
                'icon' => 'bi bi-clock-history fs-3',
                'child' => [],
                'all_routes' => ['admin.reports.module_view_time'],
            ],
            [
                'route' => route('admin.certificate_logs'),
                'name' => 'Certificate Logs',
                'icon' => 'bi bi-award fs-3',
                'child' => [],
                'all_routes' => ['admin.certificate_logs'],
            ],
            [
                'route' => route('admin.live_questions.logs'),
                'name' => 'Poll / Quiz Logs',
                'icon' => 'bi bi-list-check fs-3',
                'child' => [],
                'all_routes' => ['admin.live_questions.logs', 'admin.live_questions.logs.datatable', 'admin.live_questions.logs.export'],
            ],
        ];
    }

    return $module;
}

function get_route($route)
{
    return $route;
}

function is_active_module(array $names, $nlevel = false): string
{
    $current = Route::currentRouteName();

    $isActive = in_array($current, $names);

    return $isActive
        ? ($nlevel ? "here show" : "active")
        : '';
}

function echo_extra_for_site_setting($extra = ""): string
{
    $string = "";
    $extra = json_decode($extra);
    if (!empty($extra) && (is_array($extra) || is_object($extra))) {
        foreach ($extra as $key => $item) {
            $string .= $key . '="' . $item . '" ';
        }
    }
    return $string;
}

function upload_file($file_name = "", $path = "")
{
    $file = "";
    $request = \request();
    if ($request->hasFile($file_name) && $path) {

        $file = $request->file($file_name)->store($path, 'local');
    } else {
        echo 'Provide Valid Const from web controller';
        die();
    }
    return $file;
}

function success_session($value = ""): void
{
    session()->flash('success', ucfirst($value));
}

function error_session($value = ""): void
{
    session()->flash('error', ucfirst($value));
}

function success_error_view_generator(): string
{
    $content = "";
    if (session()->has('error')) {
        $content = '<div class="alert alert-danger alert-dismissible d-flex flex-column flex-sm-row p-5 mb-10">
                    <span class="svg-icon svg-icon-2hx svg-icon-dark me-4 mb-5 mb-sm-0"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
															<path opacity="0.3" d="M20.5543 4.37824L12.1798 2.02473C12.0626 1.99176 11.9376 1.99176 11.8203 2.02473L3.44572 4.37824C3.18118 4.45258 3 4.6807 3 4.93945V13.569C3 14.6914 3.48509 15.8404 4.4417 16.984C5.17231 17.8575 6.18314 18.7345 7.446 19.5909C9.56752 21.0295 11.6566 21.912 11.7445 21.9488C11.8258 21.9829 11.9129 22 12.0001 22C12.0872 22 12.1744 21.983 12.2557 21.9488C12.3435 21.912 14.4326 21.0295 16.5541 19.5909C17.8169 18.7345 18.8277 17.8575 19.5584 16.984C20.515 15.8404 21 14.6914 21 13.569V4.93945C21 4.6807 20.8189 4.45258 20.5543 4.37824Z" fill="black"></path>
															<path d="M10.5606 11.3042L9.57283 10.3018C9.28174 10.0065 8.80522 10.0065 8.51412 10.3018C8.22897 10.5912 8.22897 11.0559 8.51412 11.3452L10.4182 13.2773C10.8099 13.6747 11.451 13.6747 11.8427 13.2773L15.4859 9.58051C15.771 9.29117 15.771 8.82648 15.4859 8.53714C15.1948 8.24176 14.7183 8.24176 14.4272 8.53714L11.7002 11.3042C11.3869 11.6221 10.874 11.6221 10.5606 11.3042Z" fill="black"></path>
														</svg></span>
                        <div class="d-flex flex-column text-light pe-0 pe-sm-10">
                            <h4 class="mb-1 text-dark">Error</h4>
                            <span class="text-dark">' . session('error') . '</span>
                            </div>
                            <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto" data-bs-dismiss="alert">
                            <span class="svg-icon svg-icon-2x svg-icon-dark">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                                                    <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1" transform="rotate(-45 6 17.3137)" fill="black"></rect>
                                                                                    <rect x="7.41422" y="6" width="16" height="2" rx="1" transform="rotate(45 7.41422 6)" fill="black"></rect>
                                                                                </svg>
                                                                                </span>
                        </button>
                    </div>';
    } elseif (session()->has('success')) {
        $content = '<div class="alert alert-success alert-dismissible d-flex flex-column flex-sm-row p-5 mb-10">
                    <span class="svg-icon svg-icon-2hx svg-icon-dark me-4 mb-5 mb-sm-0"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
															<path opacity="0.3" d="M20.5543 4.37824L12.1798 2.02473C12.0626 1.99176 11.9376 1.99176 11.8203 2.02473L3.44572 4.37824C3.18118 4.45258 3 4.6807 3 4.93945V13.569C3 14.6914 3.48509 15.8404 4.4417 16.984C5.17231 17.8575 6.18314 18.7345 7.446 19.5909C9.56752 21.0295 11.6566 21.912 11.7445 21.9488C11.8258 21.9829 11.9129 22 12.0001 22C12.0872 22 12.1744 21.983 12.2557 21.9488C12.3435 21.912 14.4326 21.0295 16.5541 19.5909C17.8169 18.7345 18.8277 17.8575 19.5584 16.984C20.515 15.8404 21 14.6914 21 13.569V4.93945C21 4.6807 20.8189 4.45258 20.5543 4.37824Z" fill="black"></path>
															<path d="M10.5606 11.3042L9.57283 10.3018C9.28174 10.0065 8.80522 10.0065 8.51412 10.3018C8.22897 10.5912 8.22897 11.0559 8.51412 11.3452L10.4182 13.2773C10.8099 13.6747 11.451 13.6747 11.8427 13.2773L15.4859 9.58051C15.771 9.29117 15.771 8.82648 15.4859 8.53714C15.1948 8.24176 14.7183 8.24176 14.4272 8.53714L11.7002 11.3042C11.3869 11.6221 10.874 11.6221 10.5606 11.3042Z" fill="black"></path>
														</svg></span>
                        <div class="d-flex flex-column text-light pe-0 pe-sm-10">
                            <h4 class="mb-1 text-dark">Success</h4>
                            <span class="text-dark">' . session('success') . '</span>
                            </div>
                            <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto" data-bs-dismiss="alert">
                            <span class="svg-icon svg-icon-2x svg-icon-dark">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                                                    <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1" transform="rotate(-45 6 17.3137)" fill="black"></rect>
                                                                                    <rect x="7.41422" y="6" width="16" height="2" rx="1" transform="rotate(45 7.41422 6)" fill="black"></rect>
                                                                                </svg>
                                                                                </span>
                        </button>
                    </div>';
    }
    return $content;
}

function get_error_html($error): string
{
    $content = "";
    if ($error->any() !== null && $error->any()) {
        foreach ($error->all() as $key => $value) {
            $content .= '<div class="alert alert-danger alert-dismissible mb-1" role="alert">';
            $content .= '<div class="alert-text">' . $value . '</div>';
            $content .= '<div class="alert-close"><i class="flaticon2-cross kt-icon-sm" data-dismiss="alert"></i></div>';
            $content .= '</div>';
        }
    }
    return $content;
}

if (!function_exists('un_link_file')) {
    function un_link_file($image_name = ""): true|Exception|string
    {
        $pass = true;
        if (!empty($image_name)) {
            try {
                $default_url = \Illuminate\Support\Facades\URL::to('/');
                $get_default_images = asset('public/assets/media/avatars/blank.png');
                $file_name = str_replace($default_url, '', $image_name);
                $default_image_list = is_array($get_default_images) ? str_replace($default_url, '', array_values($get_default_images)) : [];
                if (!in_array($file_name, $default_image_list)) {
                    Storage::delete($file_name);
                }
            } catch (Exception $exception) {
                $pass = $exception;
            }
        } else {
            $pass = 'Empty Field Name';
        }
        return $pass;
    }
}

if (!function_exists('pageTitle')) {
    function pageTitle()
    {
        $route = request()->route()?->getName();

        if (!$route) {
            return 'Dashboard';
        }

        $parts = explode('.', $route);

        $ignore = ['index', 'create', 'edit', 'store', 'update', 'destroy', 'show'];

        foreach ($parts as $part) {
            if (!in_array($part, $ignore)) {
                return ucfirst(str_replace('_', ' ', $part));
            }
        }

        return 'Dashboard';
    }
}

if (!function_exists('siteSetting')) {
    function siteSetting($key, $default = null)
    {
        static $settings;

        if ($settings === null) {
            try {
                $settings = DB::table('general_settings')
                    ->where('status', 'active')
                    ->pluck('value', 'unique_name')
                    ->all();
            } catch (\Throwable $exception) {
                $settings = [];
            }
        }

        $value = $settings[$key] ?? null;

        return $value !== null && $value !== '' ? $value : $default;
    }
}

if (!function_exists('siteSettingAsset')) {
    function siteSettingAsset(string $key, string $fallback): string
    {
        $filename = siteSetting($key);

        if ($filename) {
            $path = 'site_settings/' . ltrim($filename, '/');

            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                return asset('storage/' . $path);
            }
        }

        return asset(ltrim($fallback, '/'));
    }
}

function is_active_header_menu($name): string
{
    $current = Route::currentRouteName();

    $isActive = ($current == $name);

    return $isActive
        ? "active"
        : '';
}
