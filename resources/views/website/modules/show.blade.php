@extends('layouts.website')
@section('standalone_page', 'true')

@push('styles')
    <style>
        .depth-room-header{position:sticky;top:0;z-index:1030;background:rgba(255,255,255,.97);border-bottom:1px solid #e1e5ec;backdrop-filter:blur(10px)}
        .depth-room-header__top{max-width:1260px;margin:0 auto;padding:12px 28px;display:flex;align-items:center;justify-content:space-between;gap:24px}
        .depth-room-logo{display:block;width:auto;height:48px}
        .depth-room-user{display:flex;align-items:center;gap:20px;color:#1f2a44;font-size:12px;font-weight:700;letter-spacing:.04em}
        .depth-room-logout{display:inline-flex;align-items:center;justify-content:center;padding:10px 16px;border:1px solid #d2d8e2;border-radius:10px;color:#1f2a44;text-decoration:none;text-transform:uppercase;letter-spacing:.08em;font-size:11px;font-weight:800;background:#fff}
        .depth-room-logout:hover{border-color:#d71920;color:#d71920}
        .depth-room-tabs{border-top:1px solid #e1e5ec;overflow-x:auto;scrollbar-width:none}
        .depth-room-tabs::-webkit-scrollbar{display:none}
        .depth-room-tabs__inner{max-width:1260px;margin:0 auto;padding:0 28px;display:flex;align-items:center}
        .depth-room-tab{position:relative;display:block;padding:14px 16px;color:#6e7587;text-decoration:none;text-transform:uppercase;letter-spacing:.08em;font-size:12px;font-weight:700;white-space:nowrap}
        .depth-room-tab:hover,.depth-room-tab.is-active{color:#1f2a44}
        .depth-room-tab::after{content:"";position:absolute;left:16px;right:16px;bottom:0;height:2px;background:#d71920;transform:scaleX(0);transform-origin:left;transition:transform .2s ease}
        .depth-room-tab:hover::after,.depth-room-tab.is-active::after{transform:scaleX(1)}
        .module-comments-panel.simple-comments{border:1px solid #e1e5ec;border-radius:16px;padding:26px;margin-top:28px;box-shadow:0 10px 28px rgba(31,42,68,.05)}
        .simple-comments .module-comments-header{padding-bottom:20px;margin-bottom:20px;border-bottom:1px solid #e7eaf0}
        .simple-comments .module-comment-composer{display:grid;grid-template-columns:44px minmax(0,1fr);gap:14px}
        .simple-comments .module-comment-avatar{width:44px;height:44px;border-radius:50%;display:grid;place-items:center;background:#fff0f1;color:#d71920;font-weight:800}
        .simple-comments .module-comments-stream{margin-top:24px}
        .simple-comments .module-comment-item{display:grid;grid-template-columns:44px minmax(0,1fr);gap:14px;padding:18px 0;border-top:1px solid #edf0f4}
        .simple-comments .module-comment-item:first-child{border-top:0}
        .simple-comments .module-comment-topline{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:6px}
        .simple-comments .module-comment-topline span{color:#7c8494;font-size:12px}
        .simple-comments .module-comment-body p{margin:0;color:#4f5768;white-space:pre-wrap}
        .simple-comments .module-comments-empty{padding:24px;text-align:center;color:#7c8494;background:#f7f8fa;border-radius:12px}
        @media(max-width:760px){.depth-room-header__top,.depth-room-tabs__inner{padding-left:18px;padding-right:18px}.depth-room-logo{height:38px}.depth-room-user{gap:10px}.depth-room-user__name{display:none}.depth-room-tab{padding:12px 13px;font-size:10px}.module-comments-panel.simple-comments{padding:20px}.simple-comments .module-comment-composer,.simple-comments .module-comment-item{grid-template-columns:36px minmax(0,1fr)}.simple-comments .module-comment-avatar{width:36px;height:36px}}
    </style>
@endpush

@section('body')
    @php
        $thumbnail = $module->thumbnail ? asset('storage/' . $module->thumbnail) : 'https://images.unsplash.com/photo-1579684385127-1ef15d508118?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80';
        $moduleAgenda = collect($module->speakers ?: [])->map(function ($row) {
            if (isset($row['name']) && !isset($row['names'])) {
                return [
                    'type' => 'person',
                    'title' => '',
                    'role' => $row['designation'] ?? 'Speaker',
                    'names' => $row['name'] ?? '',
                ];
            }

            return [
                'type' => $row['type'] ?? 'person',
                'title' => $row['title'] ?? '',
                'role' => $row['role'] ?? '',
                'names' => $row['names'] ?? '',
            ];
        })->filter(fn($row) => filled($row['title']) || filled($row['role']) || filled($row['names']))->values();
        $primarySpeaker = $moduleAgenda->firstWhere('type', 'person') ?: [
            'names' => 'EPIC Faculty',
            'role' => 'Faculty',
        ];
        $speakerAvatar = !empty($primarySpeaker['image'])
            ? asset('storage/' . $primarySpeaker['image'])
            : 'https://ui-avatars.com/api/?name=' . urlencode($primarySpeaker['names'] ?? 'EPIC Faculty') . '&background=random';
        $moduleBrands = collect($module->brands ?: [])->filter(fn($brand) => filled($brand['label']) || filled($brand['logo'] ?? null))->values();
        $fullRecordingResource = $module->resourceDocuments->firstWhere('category', 'full_recording');
        $recordingUrl = $module->video_url
            ?: ($fullRecordingResource?->external_url
                ?: ($fullRecordingResource?->file ? asset('storage/' . $fullRecordingResource->file) : null));
        $youtubeEmbedUrl = \App\Models\Module::youtubeEmbedUrl($recordingUrl);
        $directVideoUrl = $recordingUrl && !$youtubeEmbedUrl ? $recordingUrl : null;
        $certificateUnlocked = (bool) $progress->video_completed_at;
        $youtubeApiUrl = fn(?string $url) => $url ? $url . (str_contains($url, '?') ? '&' : '?') . 'enablejsapi=1&playsinline=1' : null;
        $archiveSegments = collect($module->archive_segments ?: [])
            ->map(function ($segment) use ($module, $youtubeApiUrl) {
                $embedUrl = \App\Models\Module::youtubeEmbedUrl($segment['youtube_url'] ?: $module->video_url, $segment['start_time'] ?? null);

                return [
                    'topic' => $segment['topic'] ?? 'Topic Video',
                    'youtube_url' => $segment['youtube_url'] ?? '',
                    'start_time' => $segment['start_time'] ?? '',
                    'embed_url' => $youtubeApiUrl($embedUrl),
                ];
            })
            ->filter(fn($segment) => filled($segment['topic']) && filled($segment['embed_url']))
            ->values();
        $archiveAvailable = $module->getRawOriginal('end_at')
            && \Carbon\CarbonImmutable::parse($module->getRawOriginal('end_at'), 'UTC')->lte(now('UTC'))
            && $archiveSegments->isNotEmpty();
        $playerEmbedUrl = $youtubeEmbedUrl
            ? $youtubeApiUrl($youtubeEmbedUrl)
            : ($archiveAvailable ? $archiveSegments->first()['embed_url'] : null);
        $keyTakeaways = collect(preg_split('/\r\n|\r|\n/', (string) $module->key_takeaways))
            ->map(fn($takeaway) => trim($takeaway))
            ->filter()
            ->values();
        $feedbackSubmitted = filled($progress->answers);
    @endphp

    @include('partials.website.depth_member_header')

    <section class="py-4 py-lg-5">
        <div class="container">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="video-breadcrumb mb-2">
                        <a href="{{ route('modules') }}">Chapters</a> <span class="mx-1">/</span> <span class="text-dark-green fw-semibold">{{ $module->title }}</span>
                    </div>
                    <h2 class="fw-bolder text-dark-green">{{ $module->title }}</h2>
                </div>
            </div>

            <div class="row g-4 g-lg-5">
                <div class="col-lg-8">
                    <div class="video-wrapper mb-4 shadow-sm">
                        @if($playerEmbedUrl)
                            <div class="ratio ratio-16x9">
                                <iframe
                                    id="moduleVideoFrame"
                                    src="{{ $playerEmbedUrl }}"
                                    title="{{ $module->title }}"
                                    data-complete-url="{{ route('modules.complete_video', $module->slug) }}"
                                    data-is-completed="{{ $progress->video_completed_at ? '1' : '0' }}"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                    allowfullscreen
                                ></iframe>
                            </div>
                        @elseif($directVideoUrl)
                            <div class="ratio ratio-16x9 bg-dark">
                                <video
                                    id="moduleDirectVideo"
                                    src="{{ $directVideoUrl }}"
                                    data-complete-url="{{ route('modules.complete_video', $module->slug) }}"
                                    data-is-completed="{{ $progress->video_completed_at ? '1' : '0' }}"
                                    controls
                                    playsinline
                                    preload="metadata"
                                    class="w-100 h-100"
                                ></video>
                            </div>
                        @else
                            <img src="{{ $thumbnail }}" alt="Video Thumbnail" class="img-fluid w-100 video-thumbnail">
                            <div class="video-play-btn shadow" id="playVideoBtn"></div>
                        @endif
                        <div class="video-time">{{ $module->duration ?: 'Chapter Video' }}</div>
                    </div>

                    <ul class="nav nav-tabs custom-video-tabs" id="moduleTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab" aria-controls="overview" aria-selected="true">Overview</button>
                        </li>
                        @if($keyTakeaways->isNotEmpty())
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="takeaways-tab" data-bs-toggle="tab" data-bs-target="#takeaways" type="button" role="tab" aria-controls="takeaways" aria-selected="false">Key Takeaways</button>
                            </li>
                        @endif

                        @if($archiveAvailable)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="topic-videos-tab" data-bs-toggle="tab" data-bs-target="#topic-videos" type="button" role="tab" aria-controls="topic-videos" aria-selected="false">Topic Videos</button>
                            </li>
                        @endif
                    </ul>

                    <div class="tab-content" id="moduleTabContent">
                        <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                            <div class="text-muted video-desc-text mb-4 module-overview-content">
                                {!! nl2br(e($module->overview ?: 'Chapter overview will be available soon.')) !!}
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="info-pill-box bg-white">Duration: {{ $module->duration ?: 'Chapter' }}</div>
                                </div>
                            </div>
                        </div>
                        @if($keyTakeaways->isNotEmpty())
                            <div class="tab-pane fade" id="takeaways" role="tabpanel" aria-labelledby="takeaways-tab">
                                <ul class="text-muted video-desc-text">
                                    @foreach($keyTakeaways as $takeaway)
                                        <li>{{ $takeaway }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if($archiveAvailable)
                            <div class="tab-pane fade" id="topic-videos" role="tabpanel" aria-labelledby="topic-videos-tab">
                                <div class="topic-video-list bg-white">
                                    @foreach($archiveSegments as $segment)
                                        <button
                                            type="button"
                                            class="topic-video-item {{ $loop->first ? 'active' : '' }}"
                                            data-video-src="{{ $segment['embed_url'] }}"
                                            data-video-title="{{ $segment['topic'] ?: $module->title }}"
                                        >
                                            <span class="topic-video-count">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                            <span class="topic-video-copy">
                                                <span class="topic-video-title">{{ $segment['topic'] ?: 'Topic Video' }}</span>
                                                @if(!empty($segment['start_time']))
                                                    <span class="topic-video-time">Starts at {{ $segment['start_time'] }}</span>
                                                @endif
                                            </span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <section class="module-comments-panel simple-comments bg-white">
                        <div class="module-comments-header">
                            <div>
                                <h5 class="fw-bold text-dark-green mb-1">Comments</h5>
                                <p class="text-muted small mb-0">Send your comment to the admin.</p>
                            </div>
                        </div>

                        <form class="module-comment-form" action="{{ route('modules.comments.store', $module->slug) }}" method="POST">
                            @csrf
                            <div class="module-comment-composer">
                                <div class="module-comment-avatar">{{ mb_substr(Auth::guard('web')->user()?->name ?: 'D', 0, 1) }}</div>
                                <div class="module-comment-input-wrap">
                                    <textarea
                                        name="comment"
                                        class="form-control custom-form-control"
                                        rows="3"
                                        maxlength="1000"
                                        placeholder="Write a comment..."
                                        required
                                    >{{ old('comment') }}</textarea>
                                    <div class="d-flex justify-content-between align-items-center mt-3 gap-3">
                                        <span class="text-muted small">Keep it helpful and on topic.</span>
                                        <button type="submit" class="btn btn-primary-custom px-4 py-2 fw-semibold">
                                            <i class="bi bi-send me-2"></i>Post
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </section>
                </div>

                <div class="col-lg-4">
                    @if($moduleBrands->isNotEmpty())
                        <div class="card module-brand-slider-card bg-white p-4 mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <span class="badge-custom d-inline-block px-3 py-2 mb-2 rounded-pill fw-bold">SUPPORTED BY</span>
                                    <h5 class="fw-bold text-dark-green mb-0">Brand Partners</h5>
                                </div>
                                @if($moduleBrands->count() > 1)
                                    <div class="d-flex gap-2">
                                        <button class="btn module-brand-nav" type="button" data-bs-target="#moduleBrandSlider" data-bs-slide="prev" aria-label="Previous brand">
                                            <i class="bi bi-chevron-left"></i>
                                        </button>
                                        <button class="btn module-brand-nav" type="button" data-bs-target="#moduleBrandSlider" data-bs-slide="next" aria-label="Next brand">
                                            <i class="bi bi-chevron-right"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>

                            <div id="moduleBrandSlider" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3500">
                                <div class="carousel-inner">
                                    @foreach($moduleBrands as $brand)
                                        <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                            <div class="module-brand-slide text-center">
                                                @if(!empty($brand['logo']))
                                                    <div class="module-brand-slide-logo mx-auto mb-3">
                                                        <img src="{{ asset('storage/' . $brand['logo']) }}" alt="{{ $brand['label'] ?? 'Brand logo' }}">
                                                    </div>
                                                @endif
                                                @if(!empty($brand['label']))
                                                    <h6 class="fw-bold text-dark-green mb-0">{{ $brand['label'] }}</h6>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                @if($moduleBrands->count() > 1)
                                    <div class="carousel-indicators module-brand-indicators">
                                        @foreach($moduleBrands as $brand)
                                            <button type="button" data-bs-target="#moduleBrandSlider" data-bs-slide-to="{{ $loop->index }}" class="{{ $loop->first ? 'active' : '' }}" aria-current="{{ $loop->first ? 'true' : 'false' }}" aria-label="Brand {{ $loop->iteration }}"></button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="card module-brand-slider-card bg-white p-4 mb-4 text-center">
                            <span class="badge-custom d-inline-block px-3 py-2 mb-3 rounded-pill fw-bold">SUPPORTED BY</span>
                            <div class="module-brand-slide-logo mx-auto mb-3">
                                <img src="{{ siteSettingAsset('site_logo', 'web/depth/assets/hetero.png') }}" alt="Hetero" class="site-brand-logo mx-auto" width="254" height="211">
                            </div>
                            <h6 class="fw-bold text-dark-green mb-0">DEPTH Microsite</h6>
                        </div>
                    @endif


                    <div class="card quiz-widget p-4 mb-4">
                        <h5 class="fw-bold mb-2">Module Feedback</h5>
                        @if(!$progress->video_completed_at)
                            <p class="quiz-unlock-text mb-3" id="videoAutoCompleteText">Start the video to complete your progress.</p>
                            <div class="d-grid gap-2">
                                {{--<a href="{{ route('modules.certificate', $module->slug) }}" class="btn btn-primary-custom w-100 py-2 d-none" id="videoCertificateLink">Download Certificate</a>--}}
                                <a href="{{ route('modules.quiz', $module->slug) }}" class="btn module-feedback-btn w-100 py-2 d-none" id="videoFeedbackLink">Give Feedback</a>
                            </div>
                            <button type="button" class="btn btn-locked w-100 py-2" id="videoFeedbackLocked" disabled>Feedback Locked</button>
                        @elseif($certificateUnlocked)
                            <p class="quiz-unlock-text mb-3">Video completed {{ $progress->video_completed_at->format('d M Y, h:i A') }}</p>
                            <div class="d-grid gap-2">
                                {{--<a href="{{ route('modules.certificate', $module->slug) }}" class="btn btn-primary-custom w-100 py-2">Download Certificate</a>--}}
                                <a href="{{ route('modules.quiz', $module->slug) }}" class="btn module-feedback-btn w-100 py-2">{{ $feedbackSubmitted ? 'View Feedback' : 'Give Feedback' }}</a>
                            </div>
                        @else
                            <p class="quiz-unlock-text mb-3">Video completed {{ $progress->video_completed_at->format('d M Y, h:i A') }}</p>
                            <div class="d-grid gap-2">
                                <a href="{{ route('modules.certificate', $module->slug) }}" class="btn btn-primary-custom w-100 py-2">Download Certificate</a>
                                <a href="{{ route('modules.quiz', $module->slug) }}" class="btn module-feedback-btn w-100 py-2">Give Feedback</a>
                            </div>
                        @endif
                    </div>



                    <div class="card tracker-widget bg-white p-4">
                        <h6 class="fw-bold text-dark-green mb-4">Chapter Progress</h6>
                        <div class="tracker-list">
                            <div class="tracker-item">
                                <div class="tracker-circle {{ $progress->video_completed_at ? 'done' : 'pending' }}" id="videoProgressCircle"></div>
                                <div class="d-flex justify-content-between flex-grow-1 align-items-center">
                                    <span class="fw-bold text-dark-green text-sm">Watch Video</span>
                                    <span class="text-primary-green fw-bold text-xs" id="videoProgressStatus">{{ $progress->video_completed_at ? 'Done' : 'Available' }}</span>
                                </div>
                            </div>
                            <div class="tracker-item">
                                <div class="tracker-circle {{ $certificateUnlocked ? 'done' : 'pending' }}" id="certificateProgressCircle"></div>
                                <span class="{{ $certificateUnlocked ? 'fw-bold text-dark-green' : 'text-muted' }} text-sm" id="certificateProgressText">Certificate</span>
                            </div>
                            <div class="tracker-item">
                                <div class="tracker-circle {{ $progress->progress_percent == 100 ? 'done' : 'pending' }}" id="overallProgressCircle"></div>
                                <span class="{{ $progress->progress_percent == 100 ? 'fw-bold text-dark-green' : 'text-muted' }} text-sm" id="overallProgressText">Complete {{ $progress->progress_percent }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if($nextModule)
        <section class="py-4 py-lg-5 border-top">
            <div class="container">
                <h6 class="next-series-title mb-4">NEXT CHAPTER</h6>
                <div class="row">
                    <div class="col-lg-8">
                        <div class="next-series-wrapper p-3 p-md-4 d-flex align-items-center">
                            <div class="next-series-img-placeholder me-3 me-md-4"></div>
                            <div>
                                <span class="text-muted text-xs text-uppercase fw-bold mb-1 d-block">NEXT CHAPTER</span>
                                <h5 class="fw-bold text-dark-green mb-1">{{ $nextModule->title }}</h5>
                                <span class="text-muted small">
                                    Available
                                    @if($nextModule->start_at_utc_iso || $nextModule->end_at_utc_iso)
                                        <span
                                            data-local-datetime-range
                                            data-local-start="{{ $nextModule->start_at_utc_iso }}"
                                            data-local-end="{{ $nextModule->end_at_utc_iso }}"
                                        >{{ optional($nextModule->start_at)->format('M d, Y h:i A') }} - {{ optional($nextModule->end_at)->format('M d, Y h:i A') }}</span>
                                    @else
                                        To be announced
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));

        let moduleYoutubePlayer = null;
        let moduleVideoCompletionRequestSent = false;

        function markModuleVideoCompleted() {
            const player = document.getElementById('moduleVideoFrame') || document.getElementById('moduleDirectVideo');
            if (!player || player.dataset.isCompleted === '1' || moduleVideoCompletionRequestSent) {
                return;
            }

            moduleVideoCompletionRequestSent = true;

            fetch(player.dataset.completeUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Unable to mark video completed');
                    }

                    return response.json();
                })
                .then(data => {
                    player.dataset.isCompleted = '1';

                    const unlockText = document.getElementById('videoAutoCompleteText');
                    const lockedButton = document.getElementById('videoFeedbackLocked');
                    /*const videoCertificateLink = document.getElementById('videoCertificateLink');*/
                    const videoFeedbackLink = document.getElementById('videoFeedbackLink');
                    const certificateDownloadLink = document.getElementById('certificateDownloadLink');
                    const certificateLockedWrap = document.getElementById('certificateLockedWrap');
                    const videoProgressCircle = document.getElementById('videoProgressCircle');
                    const videoProgressStatus = document.getElementById('videoProgressStatus');
                    const certificateProgressCircle = document.getElementById('certificateProgressCircle');
                    const certificateProgressText = document.getElementById('certificateProgressText');
                    const overallProgressCircle = document.getElementById('overallProgressCircle');
                    const overallProgressText = document.getElementById('overallProgressText');

                    if (unlockText) {
                        unlockText.textContent = data.completed_at ? `Video completed ${data.completed_at}` : 'Video completed';
                    }

                    if (data.certificate_url) {
                        /*videoCertificateLink?.setAttribute('href', data.certificate_url);*/
                        certificateDownloadLink?.setAttribute('href', data.certificate_url);
                    }

                    lockedButton?.classList.add('d-none');
                    /*videoCertificateLink?.classList.remove('d-none');*/
                    videoFeedbackLink?.classList.remove('d-none');
                    certificateDownloadLink?.classList.remove('d-none');
                    certificateLockedWrap?.classList.add('d-none');
                    videoProgressCircle?.classList.remove('pending');
                    videoProgressCircle?.classList.add('done');
                    videoProgressStatus && (videoProgressStatus.textContent = 'Done');
                    certificateProgressCircle?.classList.remove('pending');
                    certificateProgressCircle?.classList.add('done');
                    certificateProgressText?.classList.remove('text-muted');
                    certificateProgressText?.classList.add('fw-bold', 'text-dark-green');
                    overallProgressCircle?.classList.remove('pending');
                    overallProgressCircle?.classList.add('done');
                    overallProgressText && (overallProgressText.textContent = `Complete ${data.progress_percent || 100}%`);
                    overallProgressText?.classList.remove('text-muted');
                    overallProgressText?.classList.add('fw-bold', 'text-dark-green');
                })
                .catch(() => {
                    moduleVideoCompletionRequestSent = false;
                });
        }

        function initModuleYoutubePlayer() {
            const frame = document.getElementById('moduleVideoFrame');
            if (!frame || !window.YT || !YT.Player || moduleYoutubePlayer) {
                return;
            }

            moduleYoutubePlayer = new YT.Player('moduleVideoFrame', {
                events: {
                    onStateChange(event) {
                        if (event.data === YT.PlayerState.PLAYING) {
                            markModuleVideoCompleted();
                        }
                    }
                }
            });
        }

        window.onYouTubeIframeAPIReady = initModuleYoutubePlayer;

        if (document.getElementById('moduleVideoFrame')) {
            const youtubeApiScript = document.createElement('script');
            youtubeApiScript.src = 'https://www.youtube.com/iframe_api';
            document.head.appendChild(youtubeApiScript);
        }

        document.getElementById('moduleDirectVideo')?.addEventListener('play', markModuleVideoCompleted, {once: true});

        document.querySelectorAll('.topic-video-item').forEach(button => {
            button.addEventListener('click', () => {
                const frame = document.getElementById('moduleVideoFrame');
                if (!frame || !button.dataset.videoSrc) {
                    return;
                }

                if (moduleYoutubePlayer && typeof moduleYoutubePlayer.loadVideoByUrl === 'function') {
                    moduleYoutubePlayer.loadVideoByUrl(button.dataset.videoSrc);
                } else {
                    frame.src = button.dataset.videoSrc;
                }

                frame.title = button.dataset.videoTitle || frame.title;
                document.querySelectorAll('.topic-video-item').forEach(item => item.classList.remove('active'));
                button.classList.add('active');
                frame.scrollIntoView({behavior: 'smooth', block: 'center'});
            });
        });

        (() => {
            const heartbeatUrl = '{{ route('modules.view_heartbeat', $module->slug) }}';
            const sessionKey = 'module-view-session-{{ $module->id }}';
            const sessionId = sessionStorage.getItem(sessionKey) || (crypto.randomUUID ? crypto.randomUUID() : `${Date.now()}-${Math.random()}`);
            sessionStorage.setItem(sessionKey, sessionId);

            let lastPingAt = Date.now();
            let lastActivityAt = Date.now();
            const markActivity = () => {
                lastActivityAt = Date.now();
            };

            ['mousemove', 'keydown', 'scroll', 'touchstart', 'click'].forEach(eventName => {
                window.addEventListener(eventName, markActivity, {passive: true});
            });

            const sendHeartbeat = () => {
                if (document.hidden || Date.now() - lastActivityAt > 120000) {
                    lastPingAt = Date.now();
                    return;
                }

                const now = Date.now();
                const elapsedSeconds = Math.max(1, Math.min(60, Math.round((now - lastPingAt) / 1000)));
                lastPingAt = now;

                fetch(heartbeatUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        session_id: sessionId,
                        elapsed_seconds: elapsedSeconds,
                    }),
                    keepalive: true,
                }).catch(() => {});
            };

            window.setInterval(sendHeartbeat, 30000);
            window.addEventListener('beforeunload', sendHeartbeat);
        })();
    </script>
@endpush
