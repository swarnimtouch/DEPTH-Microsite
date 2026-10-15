<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · Live room</title>
    <link rel="icon" href="{{ siteSettingAsset('Favicon', 'web/depth/assets/hetero.png') }}">
    <style>
        *{box-sizing:border-box}
        html,body{width:100%;height:100%;margin:0;background:#111827;font-family:Inter,Arial,sans-serif}
        .room-shell{display:grid;grid-template-rows:64px minmax(0,1fr);width:100%;height:100%}
        .room-bar{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:0 22px;background:#fff;border-bottom:1px solid #e1e5ec}
        .room-brand{display:flex;align-items:center;min-width:0;gap:16px}
        .room-brand img{width:auto;height:42px}
        .room-title{min-width:0;color:#1f2a44;font-size:14px;font-weight:800;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .room-exit{flex:none;display:inline-flex;align-items:center;justify-content:center;padding:10px 15px;border:1px solid #d2d8e2;border-radius:9px;color:#1f2a44;background:#fff;text-decoration:none;font-size:12px;font-weight:800}
        .room-exit:hover{border-color:#d71920;color:#d71920}
        #webinar-room{position:relative;width:100%;height:100%;min-height:0;background:#111827}
        #webinar-room iframe{display:block;width:100%;height:100%;border:0}
        .room-loading,.room-error{position:absolute;inset:0;display:grid;place-content:center;gap:14px;padding:28px;text-align:center;color:#fff}
        .room-loading span{width:36px;height:36px;margin:auto;border:3px solid rgba(255,255,255,.25);border-top-color:#fff;border-radius:50%;animation:spin .8s linear infinite}
        .room-error[hidden],.room-loading[hidden]{display:none}
        .room-error button{justify-self:center;padding:10px 18px;border:0;border-radius:8px;background:#d71920;color:#fff;font:inherit;font-weight:800;cursor:pointer}
        @keyframes spin{to{transform:rotate(360deg)}}
        @media(max-width:700px){.room-shell{grid-template-rows:56px minmax(0,1fr)}.room-bar{padding:0 12px}.room-brand img{height:34px}.room-title{display:none}.room-exit{padding:8px 11px}}
    </style>
</head>
<body>
<main class="room-shell">
    <header class="room-bar">
        <div class="room-brand">
            <img src="{{ asset('web/depth/assets/hetero.png') }}" alt="Hetero">
            <span class="room-title">{{ $module->title }}</span>
        </div>
        <a class="room-exit" href="{{ route('dashboard.live') }}">Leave room</a>
    </header>
    <section id="webinar-room" aria-label="Webinar room">
        <div class="room-loading" id="room-loading"><span></span><b>Entering webinar room…</b></div>
        <div class="room-error" id="room-error" hidden>
            <b>The room could not be loaded.</b>
            <button type="button" id="room-retry">Try again</button>
        </div>
    </section>
</main>

<script src="https://{{ $jitsiDomain }}/external_api.js"></script>
<script>
(() => {
    const room = document.getElementById('webinar-room');
    const loading = document.getElementById('room-loading');
    const error = document.getElementById('room-error');
    let api;

    function showError() {
        loading.hidden = true;
        error.hidden = false;
    }

    function enterRoom() {
        error.hidden = true;
        loading.hidden = false;

        if (typeof window.JitsiMeetExternalAPI !== 'function') {
            showError();
            return;
        }

        api?.dispose();
        api = new window.JitsiMeetExternalAPI(@json($jitsiDomain), {
            roomName: @json($roomName),
            parentNode: room,
            width: '100%',
            height: '100%',
            userInfo: {
                displayName: @json($participant->name ?: 'Participant')
            },
            configOverwrite: {
                prejoinConfig: { enabled: false },
                startWithAudioMuted: true,
                startWithVideoMuted: true,
                disableInviteFunctions: true,
                disableDeepLinking: true,
                toolbarButtons: [
                    'microphone', 'camera', 'desktop', 'fullscreen', 'hangup',
                    'chat', 'raisehand', 'tileview', 'settings', 'videoquality'
                ]
            },
            interfaceConfigOverwrite: {
                MOBILE_APP_PROMO: false,
                SHOW_JITSI_WATERMARK: false,
                SHOW_WATERMARK_FOR_GUESTS: false,
                HIDE_INVITE_MORE_HEADER: true,
                TOOLBAR_BUTTONS: [
                    'microphone', 'camera', 'desktop', 'fullscreen', 'hangup',
                    'chat', 'raisehand', 'tileview', 'settings', 'videoquality'
                ]
            }
        });

        api.addListener('videoConferenceJoined', () => { loading.hidden = true; });
        api.addListener('readyToClose', () => { window.location.assign(@json(route('dashboard.live'))); });
    }

    document.getElementById('room-retry').addEventListener('click', enterRoom);
    window.addEventListener('load', enterRoom);
})();
</script>
</body>
</html>
