<nav class="navbar navbar-expand-lg py-3 sticky-top bg-transparent-custom">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
            <img src="{{ siteSettingAsset('site_logo', 'web/depth/assets/hetero.png') }}" alt="Hetero" class="site-brand-logo" width="254" height="211">
        </a>

        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#mainNav" aria-controls="mainNav" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="offcanvas offcanvas-end" tabindex="-1" id="mainNav" aria-labelledby="mainNavLabel">

            <div class="offcanvas-header border-bottom">
                <h5 class="offcanvas-title fw-bold text-dark-green" id="mainNavLabel">Menu</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas"
                        aria-label="Close"></button>
            </div>

            <div class="offcanvas-body">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-3 gap-lg-4">
                    <li class="nav-item">
                        @auth('web')
                            <a class="nav-link {{ request()->routeIs('course.dashboard') ? 'nav-link-active fw-semibold' : 'fw-medium' }}"
                               href="{{ route('course.dashboard') }}">Home</a>
                        @else
                            <a class="nav-link {{ request()->routeIs('home') ? 'nav-link-active fw-semibold' : 'fw-medium' }}"
                               href="{{ route('home') }}">Home</a>
                        @endauth
                    </li>
                    @auth('web')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('modules*') ? 'nav-link-active fw-semibold' : 'fw-medium' }}"
                               href="{{ route('modules') }}">Chapters</a>
                        </li>
                    @endauth
                    <li class="nav-item ms-lg-3 mt-3 mt-lg-0 d-flex flex-column flex-lg-row gap-3">
                        @auth('web')
                            <a class="btn btn-outline-nav rounded-pill px-4 fw-semibold w-100 w-lg-auto"
                               href="{{ route('dashboard') }}">Dashboard</a>
                            <a class="btn btn-primary-custom rounded-pill px-4 fw-semibold w-100 w-lg-auto"
                               href="{{ route('logout') }}">Logout</a>
                        @else
                            <a class="btn btn-outline-nav rounded-pill px-4 fw-semibold w-100 w-lg-auto"
                               href="{{route('login')}}" id="loginBtn">Login</a>
                            <a class="btn btn-primary-custom rounded-pill px-4 fw-semibold w-100 w-lg-auto"
                               href="{{route('register')}}" id="registerBtn">Register</a>
                        @endauth
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
