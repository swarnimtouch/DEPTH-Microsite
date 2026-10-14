@php
    $memberTabs = [
        'overview' => ['dashboard', 'Program overview'],
        'agenda' => ['dashboard.agenda', 'Agenda'],
        'cases' => ['dashboard.cases', 'Case booklets'],
        'speakers' => ['dashboard.speakers', 'Speaker'],
        'live' => ['dashboard.live', 'Live session'],
        'hub' => ['dashboard.hub', 'Post-event hub'],
        'profile' => ['dashboard.profile', 'Profile'],
    ];
    $activeMemberTab = in_array($dashboardSection ?? 'overview', ['takeaways', 'certificate'], true)
        ? 'hub'
        : ($dashboardSection ?? 'overview');
@endphp

<header class="hdr">
    <div class="hdr-in">
        <a class="brand" href="{{ route('dashboard') }}" aria-label="DEPTH dashboard home">
            <img class="logo-hetero" src="{{ asset('web/depth/assets/hetero.png') }}" alt="Hetero">
        </a>
        <div class="hdr-right">
            <nav class="hdr-nav" aria-label="Account">
                <span class="hdr-user">{{ ($doctor ?? Auth::guard('web')->user())?->name ?: 'Participant' }}</span>
                <a class="btn btn-soft sm" href="{{ route('logout') }}">Log out</a>
            </nav>
        </div>
    </div>
    <nav class="tabs" aria-label="Programme sections">
        <div class="tabs-in">
            @foreach($memberTabs as $section => [$routeName, $label])
                <a class="tab {{ $activeMemberTab === $section ? 'active' : '' }}" href="{{ route($routeName) }}">{{ $label }}</a>
            @endforeach
        </div>
    </nav>
</header>
