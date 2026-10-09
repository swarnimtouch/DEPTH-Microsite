@php
    $doctorName = $doctor->name ?? 'Doctor';
    $doctorRole = $doctor->hospital ?: 'Heart Failure Specialist';
    $avatarName = urlencode($doctorName);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} | {{ siteSetting('site_name', 'DEPTH Microsite') }}</title>
    <link rel="icon" href="{{ siteSettingAsset('Favicon', 'web/depth/assets/hetero.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('web/css/style.css') }}?v={{ filemtime(public_path('web/css/style.css')) }}">
</head>
<body class="dashboard-horizontal">
    <div class="dashboard-navigation sticky-top">
    <header class="top-header d-flex justify-content-between align-items-center px-3 px-lg-5 border-bottom bg-white">

        <a href="{{ route('course.dashboard') }}" class="dashboard-brand d-flex align-items-center flex-shrink-0" aria-label="{{ siteSetting('site_name', 'DEPTH Microsite') }} home">
            <img src="{{ siteSettingAsset('site_logo', 'web/image/ean-logo-official.png') }}" alt="European Academy of Neurology (EAN)" class="site-brand-logo">
        </a>

        <div class="dashboard-course-title d-none d-xl-flex align-items-center flex-grow-1 px-4">
            @hasSection('dashboard_header_center')
                @yield('dashboard_header_center')
            @else
{{--                <div class="search-box position-relative w-100">--}}
{{--                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>--}}
{{--                    <input type="text" id="dashboardSearch" class="form-control custom-search-input ps-5 py-2 rounded-pill bg-light border-0" placeholder="Search chapters, doctors...">--}}
{{--                </div>--}}
            @endif
        </div>

        <button class="navbar-toggler d-lg-none border-0 shadow-none ms-auto me-3" type="button" data-bs-toggle="collapse" data-bs-target="#dashboardMenu" aria-controls="dashboardMenu" aria-expanded="false" aria-label="Toggle navigation">
            <i class="bi bi-list fs-3" aria-hidden="true"></i>
        </button>
        <div class="user-profile d-flex align-items-center py-3">
            <div class="profile-info position-relative d-flex align-items-center cursor-pointer">
                <img src="{{ $doctor->getRawOriginal('profile_image') ? $doctor->profile_image : 'https://ui-avatars.com/api/?name=' . $avatarName . '&background=random' }}" alt="Profile" class="header-profile-avatar rounded-circle me-2 border shadow-sm">
                <div class="user-details me-3 d-none d-md-block text-start">
                    <div class="fw-bold fs-6 text-dark-green lh-1">Dr. {{ $doctorName }}</div>
                    <div class="text-muted small sidebar-role-text mt-1">{{ $doctorRole }}</div>
                </div>
                <i class="bi bi-chevron-down text-muted small profile-chevron"></i>

                <div class="profile-dropdown shadow-lg rounded-3 bg-white">
                    <ul class="list-unstyled mb-0 py-2">
                        <li>
                            <a href="{{ route('profile.edit') }}" class="dropdown-item d-flex align-items-center text-dark px-3 py-2">
                                <i class="bi bi-person me-3 text-primary-green"></i> My Profile
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a href="{{ route('logout') }}" class="dropdown-item d-flex align-items-center text-danger px-3 py-2">
                                <i class="bi bi-box-arrow-right me-3"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <nav class="navbar navbar-expand-lg dashboard-menu bg-white py-0" aria-label="Main navigation">
        <div class="container-fluid px-3 px-lg-5">
            <div class="collapse navbar-collapse" id="dashboardMenu">
                <ul class="navbar-nav gap-lg-3 py-2">
                    <li class="nav-item"><a class="nav-link dashboard-menu-link {{ request()->routeIs('course.dashboard') ? 'active' : '' }}" @if(request()->routeIs('course.dashboard')) aria-current="page" @endif href="{{ route('course.dashboard') }}"><i class="bi bi-house me-2" aria-hidden="true"></i>Home</a></li>
                    <li class="nav-item"><a class="nav-link dashboard-menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif href="{{ route('dashboard') }}"><i class="bi bi-graph-up me-2" aria-hidden="true"></i>Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link dashboard-menu-link {{ request()->routeIs('modules*') ? 'active' : '' }}" @if(request()->routeIs('modules*')) aria-current="page" @endif href="{{ route('modules') }}"><i class="bi bi-book me-2" aria-hidden="true"></i>Chapters</a></li>
                    <li class="nav-item"><a class="nav-link dashboard-menu-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" @if(request()->routeIs('profile.*')) aria-current="page" @endif href="{{ route('profile.edit') }}"><i class="bi bi-person me-2" aria-hidden="true"></i>My Profile</a></li>
                </ul>
                <a href="{{ route('logout') }}" class="nav-link dashboard-menu-link text-danger ms-lg-auto my-2"><i class="bi bi-box-arrow-right me-2" aria-hidden="true"></i>Logout</a>
            </div>
        </div>
    </nav>
    </div>

    <div class="d-flex flex-column flex-grow-1">
        <div class="main-content flex-grow-1">
            <div class="container-fluid p-4 p-lg-5">
                @yield('content')
            </div>

            <footer class="custom-footer py-4 mt-auto">
                <div class="container-fluid px-4 px-lg-5">
                    <div class="text-center text-lg-end">
                        <span class="footer-text">{{ siteSetting('footer_text', '© ' . date('Y') . ' EAN Microsite.') }}</span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('web/js/local-time.js') }}"></script>
    @stack('dashboard_scripts')
    <script src="{{ asset('web/js/dashboard.js') }}"></script>
    <script type="application/javascript">
        $(function () {
            toastr.options = {
                closeButton: true,
                progressBar: true,
                positionClass: "toast-top-right",
                timeOut: 5000,
            };

            @if(session('success'))
                toastr.success(@json(session('success')));
            @endif

            @if(session('error'))
                toastr.error(@json(session('error')));
            @endif
        });
    </script>
</body>
</html>
