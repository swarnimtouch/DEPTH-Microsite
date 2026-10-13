<header class="depth-room-header">
    <div class="depth-room-header__top">
        <a href="{{ route('dashboard') }}#overview" aria-label="DEPTH dashboard home">
            <img class="depth-room-logo" src="{{ asset('web/depth/assets/hetero.png') }}" alt="Hetero">
        </a>
        <div class="depth-room-user">
            <span class="depth-room-user__name">{{ Auth::guard('web')->user()?->name ?: 'Participant' }}</span>
            <a class="depth-room-logout" href="{{ route('logout') }}">Log out</a>
        </div>
    </div>
    <nav class="depth-room-tabs" aria-label="Programme sections">
        <div class="depth-room-tabs__inner">
            <a class="depth-room-tab" href="{{ route('dashboard') }}#overview">Program overview</a>
            <a class="depth-room-tab" href="{{ route('dashboard') }}#agenda">Agenda</a>
            <a class="depth-room-tab" href="{{ route('dashboard') }}#cases">Case booklets</a>
            <a class="depth-room-tab" href="{{ route('dashboard') }}#speakers">Speaker</a>
            <a class="depth-room-tab is-active" href="{{ route('dashboard') }}#live">Live session</a>
            <a class="depth-room-tab" href="{{ route('dashboard') }}#hub">Post-event hub</a>
            <a class="depth-room-tab" href="{{ route('dashboard') }}#profile">Profile</a>
        </div>
    </nav>
</header>
