@extends('layouts.website')
@section('standalone_page', 'true')

@push('styles')
    <style>
        .depth-room-header{position:sticky;top:0;z-index:1030;background:rgba(255,255,255,.97);border-bottom:1px solid #e1e5ec;backdrop-filter:blur(10px)}
        .depth-room-header__top{max-width:1260px;margin:0 auto;padding:12px 28px;display:flex;align-items:center;justify-content:space-between;gap:24px}
        .depth-room-brand{display:flex;align-items:center;gap:16px;text-decoration:none;min-width:0}
        .depth-room-logo{display:block;width:auto;height:48px}
        .depth-room-brand__divider{width:1px;height:46px;background:#d9dee7;flex:0 0 auto}
        .depth-room-depth-logo{display:block;width:auto;height:48px;max-width:250px}
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
        .room-live-question{position:relative;border:1px solid #dce3ee!important;box-shadow:0 10px 28px rgba(31,42,68,.07);overflow:hidden}
        .room-live-question::before{content:"";position:absolute;inset:0 0 auto;height:4px;background:linear-gradient(90deg,#d71920,#667fbd)}
        .room-live-question__head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:18px}
        .room-live-question__head h5{color:#17345b;line-height:1.4}
        .live-question-kicker{display:block;color:#d71920;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.1em}
        .live-question-pulse{flex:none;padding:5px 9px;border-radius:999px;background:#fff0f1;color:#d71920;font-size:10px;font-weight:900}
        .live-question-options{display:grid;gap:10px;margin-bottom:16px}
        .live-question-option{position:relative;display:flex;align-items:center;gap:9px;padding:12px;border:1px solid #dce3ee;border-radius:9px;background:#fff;color:#263650;cursor:pointer;overflow:hidden}
        .live-question-option:has(input:checked){border-color:#d71920;background:#fff7f7}.live-question-option input{flex:none}
        .live-question-result{position:absolute;inset:0 auto 0 0;background:#eaf3ff;z-index:0}.live-question-option span,.live-question-option strong,.live-question-option input{position:relative;z-index:1}.live-question-option span{flex:1}.live-question-option strong{font-size:12px}
        .live-question-option.is-correct{border-color:#2a9762;background:#effbf5}.live-question-option.is-wrong{border-color:#d71920;background:#fff3f3}
        .live-question-message{margin-top:13px;padding:11px 12px;border-radius:8px;background:#f2f5f9;color:#34405a;font-size:13px;font-weight:700}
        .survey-room-card{border:0;background:linear-gradient(135deg,#6f91cb,#5d7fb9);color:#fff;border-radius:16px}
        .survey-room-card .btn{background:#fff;color:#234d87;border:0;font-weight:700}
        .survey-modal .modal-content{border:0;border-radius:18px;overflow:hidden;box-shadow:0 24px 70px rgba(31,42,68,.22)}
        .survey-modal .modal-header{border:0;padding:26px 30px 10px}.survey-modal .modal-body{padding:16px 30px 30px}
        .survey-progress{height:5px;background:#e8ebf0;border-radius:10px;overflow:hidden}.survey-progress span{display:block;height:100%;background:#d71920;transition:width .25s ease}
        .survey-option{display:flex;gap:12px;align-items:center;width:100%;padding:14px 16px;margin-top:10px;border:1px solid #dce2ea;border-radius:11px;background:#fff;text-align:left;color:#1f2a44}
        .survey-option:hover,.survey-option.is-selected{border-color:#d71920;background:#fff7f7}.survey-option i{margin-left:auto;color:#d71920;opacity:0}.survey-option.is-selected i{opacity:1}
        #postSurveyResponsesView .survey-option{cursor:default}#postSurveyResponsesView .survey-option:hover{box-shadow:none}#postSurveyResponsesView .survey-option.is-selected .custom-radio-circle{border:6px solid #d71920;background:#fff}
        .survey-response-list{display:grid;gap:12px}.survey-response-item{padding:14px;border:1px solid #e4e8ef;border-radius:10px}.survey-response-item strong{display:block;color:#17345b;margin-bottom:4px}
        .post-survey-panel{border:1px solid #e1e5ec;border-radius:14px;padding:26px;background:#fff}
        .certificate-ready-dialog{max-width:520px}
        @media(max-width:760px){.depth-room-header__top,.depth-room-tabs__inner{padding-left:18px;padding-right:18px}.depth-room-brand{gap:9px;flex:1 1 auto}.depth-room-logo{height:36px}.depth-room-brand__divider{height:34px}.depth-room-depth-logo{height:34px;max-width:min(36vw,150px)}.depth-room-user{gap:10px}.depth-room-user__name{display:none}.depth-room-tab{padding:12px 13px;font-size:10px}.module-comments-panel.simple-comments{padding:20px}.simple-comments .module-comment-composer,.simple-comments .module-comment-item{grid-template-columns:36px minmax(0,1fr)}.simple-comments .module-comment-avatar{width:36px;height:36px}}
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
        $postSurveyCompleted = \App\Models\SurveySubmission::where([
            'module_id' => $module->id,
            'user_id' => Auth::guard('web')->id(),
            'survey_type' => 'post',
        ])->exists();
        $certificateUnlocked = (bool) $module->certificate_enabled
            && (bool) $progress->video_completed_at
            && $postSurveyCompleted;
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
        $preSurveyState = $initialSurveyState['pre'];
        $postSurveyState = $initialSurveyState['post'];
        $initialLiveQuestion = $initialLiveQuestionState['question'];
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
                        <div class="video-time">{{ $module->display_duration ?: 'Chapter Video' }}</div>
                    </div>

                    <ul class="nav nav-tabs custom-video-tabs" id="moduleTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab" aria-controls="overview" aria-selected="true">Overview</button>
                        </li>
                        <li class="nav-item {{ $postSurveyState['enabled'] ? '' : 'd-none' }}" role="presentation" id="postSurveyTabItem">
                            <button class="nav-link" id="post-survey-tab" data-bs-toggle="tab" data-bs-target="#post-survey" type="button" role="tab" aria-controls="post-survey" aria-selected="false">Post-Event Survey</button>
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
                                    <div class="info-pill-box bg-white">Duration: {{ $module->display_duration ?: 'Chapter' }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="post-survey" role="tabpanel" aria-labelledby="post-survey-tab">
                            <section class="post-survey-panel {{ $postSurveyState['enabled'] ? '' : 'd-none' }}" id="postSurveyCard">
                                <div class="text-danger text-uppercase fw-bold small mb-2">After the session</div>
                                <h4 class="fw-bold text-dark-green mb-2">Post-event survey</h4>
                                <div id="postSurveyIntro">
                                    <p class="text-muted mb-4" id="postSurveyCardText">
                                        @if($postSurveyState['completed'])
                                            View your responses.
                                        @elseif(!$postSurveyState['eligible'])
                                            This survey will open after you complete the module.
                                        @else
                                            Please share your response after completing the module.
                                        @endif
                                    </p>
                                    <button type="button" class="btn btn-primary-custom px-4" data-open-survey="post" {{ !$postSurveyState['eligible'] && !$postSurveyState['completed'] ? 'disabled' : '' }}>
                                        {{ $postSurveyState['completed'] ? 'View responses' : (!$postSurveyState['eligible'] ? 'Available after completion' : 'Start survey') }}
                                    </button>
                                </div>
                                <div class="d-none mt-4" id="postSurveyQuestionView">
                                    <div class="d-flex justify-content-between small mb-2"><strong class="text-danger" id="postSurveyQuestionCount"></strong><span class="text-muted">Select one answer</span></div>
                                    <div class="survey-progress mb-4"><span id="postSurveyProgressBar"></span></div>
                                    <h4 class="fw-bold text-dark-green mb-3" id="postSurveyQuestionText"></h4>
                                    <div id="postSurveyOptions"></div>
                                    <div class="d-flex justify-content-between mt-4"><button type="button" class="btn btn-light" id="postSurveyPrevious">Previous</button><button type="button" class="btn btn-primary-custom px-4" id="postSurveyNext" disabled>Next</button></div>
                                </div>
                                <div class="d-none mt-4" id="postSurveyResponsesView">
                                    <div class="d-flex justify-content-between small mb-2"><strong class="text-danger" id="postResponseCount"></strong><span class="text-muted">Your response</span></div>
                                    <div class="survey-progress mb-4"><span id="postResponseProgress"></span></div>
                                    <h4 class="fw-bold text-dark-green mb-3" id="postResponseQuestion"></h4>
                                    <div id="postResponseOptions"></div>
                                    <div class="d-flex justify-content-between mt-4"><button type="button" class="btn btn-light" id="postResponsePrevious">Previous</button><button type="button" class="btn btn-primary-custom px-4" id="postResponseNext">Next question</button></div>
                                </div>
                            </section>
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


                    <div class="card survey-room-card p-4 mb-4 {{ $preSurveyState['enabled'] ? '' : 'd-none' }}" id="preSurveyCard">
                        <h5 class="fw-bold mb-2">Pre-Event Survey</h5>
                        <p class="small opacity-75 mb-3" id="preSurveyCardText">{{ $preSurveyState['completed'] ? 'View your responses.' : 'Please complete this before entering the live module.' }}</p>
                        <button type="button" class="btn w-100 py-2" data-open-survey="pre">{{ $preSurveyState['completed'] ? 'View responses' : 'Start survey' }}</button>
                    </div>

                    <div class="card quiz-widget p-4 mb-4">
                        <h5 class="fw-bold mb-2">Module Feedback</h5>
                        @if(!$progress->video_completed_at)
                            <p class="quiz-unlock-text mb-3" id="videoAutoCompleteText">Start the video to complete your progress.</p>
                            <div class="d-grid gap-2">
                                <a href="{{ route('modules.quiz', $module->slug) }}" class="btn module-feedback-btn w-100 py-2 d-none" id="videoFeedbackLink">Give Feedback</a>
                            </div>
                            <button type="button" class="btn btn-locked w-100 py-2" id="videoFeedbackLocked" disabled>Feedback Locked</button>
                        @else
                            <p class="quiz-unlock-text mb-3">Video completed {{ $progress->video_completed_at->format('d M Y, h:i A') }}</p>
                            <div class="d-grid gap-2">
                                <a href="{{ route('modules.quiz', $module->slug) }}" class="btn module-feedback-btn w-100 py-2">{{ $feedbackSubmitted ? 'View Feedback' : 'Give Feedback' }}</a>
                            </div>
                        @endif
                    </div>

                    <div
                        class="card room-live-question bg-white p-4 mb-4"
                        data-live-questions
                        data-live-questions-url="{{ route('live_questions.state', $module) }}"
                        data-live-questions-channel="live-questions.module.{{ $module->id }}"
                        data-live-question-answer-url="{{ route('live_questions.answer', ['liveQuestion' => '__QUESTION__']) }}"
                        {{ $initialLiveQuestion ? '' : 'hidden' }}
                    >
                        <div class="room-live-question__head">
                            <div>
                                <span class="live-question-kicker" data-live-question-type>{{ data_get($initialLiveQuestion, 'type') === 'quiz' ? 'Live quiz' : 'Live poll' }}</span>
                                <h5 class="fw-bold mb-0 mt-1" data-live-question-text>{{ data_get($initialLiveQuestion, 'question') }}</h5>
                            </div>
                            <span class="live-question-pulse">LIVE</span>
                        </div>
                        <form data-live-question-form>
                            <div class="live-question-options" data-live-question-options>
                                @foreach(data_get($initialLiveQuestion, 'options', []) as $option)
                                    @php
                                        $pollOptionSelected = (int) data_get($initialLiveQuestion, 'selected_option_id') === (int) $option['id'];
                                        $pollAnswered = (bool) data_get($initialLiveQuestion, 'answered');
                                        $pollResultClass = $pollAnswered && data_get($initialLiveQuestion, 'type') === 'quiz'
                                            ? ($option['is_correct'] ? 'is-correct' : ($pollOptionSelected ? 'is-wrong' : ''))
                                            : '';
                                    @endphp
                                    <label class="live-question-option {{ $pollResultClass }}">
                                        @if($pollAnswered)<i class="live-question-result" style="width:{{ $option['percent'] }}%"></i>@endif
                                        <input type="radio" name="option_id" value="{{ $option['id'] }}" @checked($pollOptionSelected) @disabled($pollAnswered)>
                                        <span>{{ $option['text'] }}</span>
                                        @if($pollAnswered)<strong>{{ $option['percent'] }}%</strong>@endif
                                    </label>
                                @endforeach
                            </div>
                            <button class="btn btn-primary-custom w-100 py-2" type="submit" data-live-question-submit {{ data_get($initialLiveQuestion, 'answered') ? 'hidden' : '' }}>Submit Answer</button>
                        </form>
                        <div class="live-question-message" data-live-question-message {{ data_get($initialLiveQuestion, 'answered') ? '' : 'hidden' }}>
                            @if(data_get($initialLiveQuestion, 'answered'))
                                @if(data_get($initialLiveQuestion, 'type') === 'quiz')
                                    {{ data_get($initialLiveQuestion, 'is_correct') ? 'Correct answer!' : 'Answer submitted. The correct option is highlighted.' }}
                                
                                @endif
                            @endif
                        </div>
                    </div>
                    <script type="application/json" id="initialLiveQuestionState">@json($initialLiveQuestionState)</script>



                    <div class="card tracker-widget bg-white p-4" id="certificateAvailability" data-status-url="{{ route('modules.certificate_status', $module->slug) }}" data-certificate-enabled="{{ $certificateUnlocked ? '1' : '0' }}">
                        <h6 class="fw-bold text-dark-green mb-3">Chapter Progress</h6>
                        <div class="d-grid {{ $certificateUnlocked ? 'mb-4' : '' }}" id="certificateActionWrap">
                            @if($certificateUnlocked)
                                <a href="{{ route('modules.certificate', $module->slug) }}" class="btn btn-primary-custom w-100 py-2" id="certificateDownloadLink">Download Certificate</a>
                            @endif
                        </div>
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
                                <span class="{{ $certificateUnlocked ? 'fw-bold text-dark-green' : 'text-muted' }} text-sm" id="certificateProgressText">{{ !$module->certificate_enabled ? 'Certificate not released' : ($certificateUnlocked ? 'Certificate' : 'Certificate pending') }}</span>
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

    <div class="modal fade survey-modal" id="moduleSurveyModal" tabindex="-1" aria-labelledby="moduleSurveyTitle" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content">
            <div class="modal-header">
                <div><div class="text-danger text-uppercase fw-bold small mb-1" id="moduleSurveyKicker"></div><h3 class="modal-title fw-bold text-dark-green" id="moduleSurveyTitle"></h3><p class="text-muted mb-0">{{ $module->title }}</p></div>
                <button type="button" class="btn-close d-none" id="moduleSurveyClose" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="surveyQuestionView">
                    <div class="d-flex justify-content-between small mb-2"><strong class="text-danger" id="surveyQuestionCount"></strong><span class="text-muted">Select one answer</span></div>
                    <div class="survey-progress mb-4"><span id="surveyProgressBar"></span></div>
                    <h4 class="fw-bold text-dark-green mb-3" id="surveyQuestionText"></h4>
                    <div id="surveyOptions"></div>
                    <div class="d-flex justify-content-between mt-4"><button type="button" class="btn btn-light" id="surveyPrevious">Previous</button><button type="button" class="btn btn-primary-custom px-4" id="surveyNext" disabled>Next</button></div>
                </div>
                <div class="d-none" id="surveyResponsesView"><div class="survey-response-list" id="surveyResponseList"></div></div>
                <div class="d-none text-center py-4" id="surveySuccessView"><i class="bi bi-check-circle-fill text-success display-4"></i><h4 class="fw-bold mt-3" id="surveySuccessTitle"></h4><p class="text-muted" id="surveySuccessText"></p><div id="surveySuccessAction"></div></div>
            </div>
        </div></div>
    </div>

    <div class="modal fade" id="certificateReadyModal" tabindex="-1" aria-labelledby="certificateReadyTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered certificate-ready-dialog">
            <div class="modal-content border-0 rounded-4 shadow-lg">
                <div class="modal-body text-center p-4">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                    <i class="bi bi-award-fill text-danger display-4"></i>
                    <h4 class="fw-bold text-dark-green mt-3" id="certificateReadyTitle">Your certificate is ready</h4>
                    <p class="text-muted mb-4">Thank you for completing the post-event survey.</p>
                    <a href="#" class="btn btn-primary-custom w-100 py-2" id="certificateReadyDownload">Download Your Certificate</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const surveyStateUrl = @json(route('modules.surveys.state', $module->slug));
        const surveyChannel = @json('surveys.module.' . $module->id);
        const initialSurveyState = @json($initialSurveyState);
        let moduleSurveyState = initialSurveyState;
        let activeSurveyType = null;
        let activeSurveyIndex = 0;
        let activeSurveyAnswers = {};
        let postSurveyIndex = 0;
        let postSurveyAnswers = {};
        let postResponseIndex = 0;
        const surveyModalElement = document.getElementById('moduleSurveyModal');
        const surveyModal = surveyModalElement ? new bootstrap.Modal(surveyModalElement) : null;
        const certificateReadyElement = document.getElementById('certificateReadyModal');
        const certificateReadyModal = certificateReadyElement ? new bootstrap.Modal(certificateReadyElement) : null;

        function escapeSurveyHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));
        }

        function updateSurveyCards() {
            ['pre', 'post'].forEach(type => {
                const state = moduleSurveyState?.[type];
                const card = document.getElementById(`${type}SurveyCard`);
                if (!card) return;
                const visible = Boolean(state?.enabled);
                card.classList.toggle('d-none', !visible);
                if (type === 'post') {
                    const tabItem = document.getElementById('postSurveyTabItem');
                    tabItem?.classList.toggle('d-none', !visible);
                    if (!visible && document.getElementById('post-survey-tab')?.classList.contains('active')) {
                        bootstrap.Tab.getOrCreateInstance(document.getElementById('overview-tab')).show();
                    }
                }
                if (!visible) return;
                const text = document.getElementById(`${type}SurveyCardText`);
                const button = card.querySelector('[data-open-survey]');
                const waitingForCompletion = type === 'post' && !state.eligible && !state.completed;
                text.textContent = state.completed
                    ? 'View your responses.'
                    : (waitingForCompletion ? 'This survey will open after you complete the module.' : (type === 'pre' ? 'Please complete this before entering the live module.' : 'Please share your response after completing the module.'));
                button.textContent = state.completed ? 'View responses' : (waitingForCompletion ? 'Available after completion' : 'Start survey');
                button.disabled = waitingForCompletion;
                if (type === 'post' && state.completed) renderPostSurveyResponses();
            });
        }

        function openRequiredSurvey() {
            if (moduleSurveyState.pre.enabled && !moduleSurveyState.pre.completed && moduleSurveyState.pre.questions.length) {
                openSurvey('pre');
            } else if (moduleSurveyState.post.enabled && moduleSurveyState.post.eligible && !moduleSurveyState.post.completed && moduleSurveyState.post.questions.length) {
                openSurvey('post');
            }
        }

        async function loadSurveyState({openRequired = false} = {}) {
            const response = await fetch(surveyStateUrl, {headers:{Accept:'application/json','X-Requested-With':'XMLHttpRequest'}, cache:'no-store'});
            if (!response.ok) throw new Error('Unable to load surveys');
            moduleSurveyState = await response.json();
            updateSurveyCards();
            if (openRequired) openRequiredSurvey();
            return moduleSurveyState;
        }

        function renderSurveyQuestion() {
            const survey = moduleSurveyState[activeSurveyType];
            const question = survey.questions[activeSurveyIndex];
            document.getElementById('surveyQuestionCount').textContent = `Question ${activeSurveyIndex + 1} of ${survey.questions.length}`;
            document.getElementById('surveyProgressBar').style.width = `${((activeSurveyIndex + 1) / survey.questions.length) * 100}%`;
            document.getElementById('surveyQuestionText').textContent = question.question;
            document.getElementById('surveyOptions').innerHTML = question.options.map(option => `<button type="button" class="survey-option ${activeSurveyAnswers[question.id] == option.id ? 'is-selected' : ''}" data-survey-option="${option.id}"><span>${escapeSurveyHtml(option.text)}</span><i class="bi bi-check-circle-fill"></i></button>`).join('');
            document.getElementById('surveyPrevious').classList.toggle('invisible', activeSurveyIndex === 0 || activeSurveyType === 'pre');
            const next = document.getElementById('surveyNext');
            next.textContent = activeSurveyIndex === survey.questions.length - 1 ? 'Submit responses' : 'Next';
            next.disabled = !activeSurveyAnswers[question.id];
        }

        function showSurveyResponses(survey) {
            document.getElementById('surveyQuestionView').classList.add('d-none');
            document.getElementById('surveySuccessView').classList.add('d-none');
            document.getElementById('surveyResponsesView').classList.remove('d-none');
            document.getElementById('surveyResponseList').innerHTML = survey.responses.map(item => `<div class="survey-response-item"><strong>${escapeSurveyHtml(item.question)}</strong><span>${escapeSurveyHtml(item.answer)}</span></div>`).join('');
        }

        function renderPostSurveyQuestion() {
            const survey = moduleSurveyState.post;
            const question = survey.questions[postSurveyIndex];
            document.getElementById('postSurveyQuestionCount').textContent = `Question ${postSurveyIndex + 1} of ${survey.questions.length}`;
            document.getElementById('postSurveyProgressBar').style.width = `${((postSurveyIndex + 1) / survey.questions.length) * 100}%`;
            document.getElementById('postSurveyQuestionText').textContent = question.question;
            document.getElementById('postSurveyOptions').innerHTML = question.options.map(option => `<button type="button" class="survey-option ${postSurveyAnswers[question.id] == option.id ? 'is-selected' : ''}" data-post-survey-option="${option.id}"><span>${escapeSurveyHtml(option.text)}</span><i class="bi bi-check-circle-fill"></i></button>`).join('');
            document.getElementById('postSurveyPrevious').classList.toggle('invisible', postSurveyIndex === 0);
            const next = document.getElementById('postSurveyNext');
            next.textContent = postSurveyIndex === survey.questions.length - 1 ? 'Submit responses' : 'Next question';
            next.disabled = !postSurveyAnswers[question.id];
        }

        function renderPostSurveyResponses() {
            const survey = moduleSurveyState.post;
            const response = survey.responses[postResponseIndex];
            document.getElementById('postSurveyIntro').classList.add('d-none');
            document.getElementById('postSurveyQuestionView').classList.add('d-none');
            document.getElementById('postSurveyResponsesView').classList.remove('d-none');
            if (!response) return;
            document.getElementById('postResponseCount').textContent = `Question ${postResponseIndex + 1} of ${survey.responses.length}`;
            document.getElementById('postResponseProgress').style.width = `${((postResponseIndex + 1) / survey.responses.length) * 100}%`;
            document.getElementById('postResponseQuestion').textContent = response.question;
            document.getElementById('postResponseOptions').innerHTML = response.options.map(option => `<div class="survey-option ${option === response.answer ? 'is-selected' : ''}"><span class="custom-radio-circle"></span><span>${escapeSurveyHtml(option)}</span>${option === response.answer ? '<i class="bi bi-check-circle-fill"></i>' : ''}</div>`).join('');
            document.getElementById('postResponsePrevious').classList.toggle('invisible', postResponseIndex === 0);
            const next = document.getElementById('postResponseNext');
            next.classList.toggle('invisible', postResponseIndex === survey.responses.length - 1);
            next.textContent = 'Next question';
        }

        function openPostSurveyInline() {
            const survey = moduleSurveyState?.post;
            if (!survey?.enabled) return;
            bootstrap.Tab.getOrCreateInstance(document.getElementById('post-survey-tab')).show();
            if (survey.completed) {
                postResponseIndex = 0;
                renderPostSurveyResponses();
                return;
            }
            if (!survey.eligible || !survey.questions.length) return;
            postSurveyIndex = 0;
            postSurveyAnswers = {};
            document.getElementById('postSurveyIntro').classList.add('d-none');
            document.getElementById('postSurveyResponsesView').classList.add('d-none');
            document.getElementById('postSurveyQuestionView').classList.remove('d-none');
            renderPostSurveyQuestion();
        }

        function openSurvey(type) {
            const survey = moduleSurveyState?.[type];
            if (!survey?.enabled || (type === 'post' && !survey.eligible)) return;
            if (type === 'post') {
                openPostSurveyInline();
                return;
            }
            activeSurveyType = type; activeSurveyIndex = 0; activeSurveyAnswers = {};
            document.getElementById('moduleSurveyKicker').textContent = type === 'pre' ? 'Before the session' : 'After the session';
            document.getElementById('moduleSurveyTitle').textContent = type === 'pre' ? 'Pre-event survey' : 'Post-event survey';
            document.getElementById('moduleSurveyClose').classList.toggle('d-none', type === 'pre' && !survey.completed);
            document.getElementById('surveyResponsesView').classList.add('d-none'); document.getElementById('surveySuccessView').classList.add('d-none');
            if (survey.completed) showSurveyResponses(survey);
            else { document.getElementById('surveyQuestionView').classList.remove('d-none'); renderSurveyQuestion(); }
            surveyModal?.show();
        }

        document.addEventListener('click', event => {
            const open = event.target.closest('[data-open-survey]');
            if (open) {
                const survey = moduleSurveyState?.[open.dataset.openSurvey];
                if (open.dataset.openSurvey === 'post') openPostSurveyInline();
                else if (survey?.completed && survey.response_url) window.location.href = survey.response_url;
                else openSurvey(open.dataset.openSurvey);
            }
            const option = event.target.closest('[data-survey-option]');
            if (option && activeSurveyType) {
                const question = moduleSurveyState[activeSurveyType].questions[activeSurveyIndex];
                activeSurveyAnswers[question.id] = Number(option.dataset.surveyOption); renderSurveyQuestion();
            }
        });
        document.addEventListener('click', event => {
            const option = event.target.closest('[data-post-survey-option]');
            if (!option || !moduleSurveyState?.post) return;
            const question = moduleSurveyState.post.questions[postSurveyIndex];
            postSurveyAnswers[question.id] = Number(option.dataset.postSurveyOption);
            renderPostSurveyQuestion();
        });
        document.getElementById('postSurveyPrevious')?.addEventListener('click', () => { if (postSurveyIndex > 0) { postSurveyIndex--; renderPostSurveyQuestion(); } });
        document.getElementById('postResponsePrevious')?.addEventListener('click', () => { if (postResponseIndex > 0) { postResponseIndex--; renderPostSurveyResponses(); } });
        document.getElementById('postResponseNext')?.addEventListener('click', () => { if (postResponseIndex < moduleSurveyState.post.responses.length - 1) { postResponseIndex++; renderPostSurveyResponses(); } });
        document.getElementById('postSurveyNext')?.addEventListener('click', async function () {
            const survey = moduleSurveyState.post;
            if (postSurveyIndex < survey.questions.length - 1) { postSurveyIndex++; renderPostSurveyQuestion(); return; }
            this.disabled = true; this.textContent = 'Saving...';
            try {
                const response = await axios.post(survey.submit_url, {answers: postSurveyAnswers}, {headers:{Accept:'application/json'}});
                await loadSurveyState();
                renderPostSurveyResponses();
                toastr.success(response.data.message);
                syncCertificateAvailability();
                if (response.data.certificate_enabled && response.data.certificate_url) {
                    document.getElementById('certificateReadyDownload').href = response.data.certificate_url;
                    certificateReadyModal?.show();
                }
            } catch (error) {
                toastr.error(error.response?.data?.message || 'Unable to submit the survey');
                renderPostSurveyQuestion();
            }
        });
        document.getElementById('surveyPrevious')?.addEventListener('click', () => { if (activeSurveyIndex > 0) { activeSurveyIndex--; renderSurveyQuestion(); } });
        document.getElementById('surveyNext')?.addEventListener('click', async function () {
            const survey = moduleSurveyState[activeSurveyType];
            if (activeSurveyIndex < survey.questions.length - 1) { activeSurveyIndex++; renderSurveyQuestion(); return; }
            this.disabled = true; this.textContent = 'Saving...';
            try {
                const response = await axios.post(survey.submit_url, {answers: activeSurveyAnswers}, {headers:{Accept:'application/json'}});
                await loadSurveyState();
                if (activeSurveyType === 'pre') {
                    surveyModal?.hide();
                    toastr.success(response.data.message);
                    return;
                }
                document.getElementById('surveyQuestionView').classList.add('d-none'); document.getElementById('surveyResponsesView').classList.add('d-none'); document.getElementById('surveySuccessView').classList.remove('d-none'); document.getElementById('moduleSurveyClose').classList.remove('d-none');
                const certificateReady = activeSurveyType === 'post' && response.data.certificate_enabled && response.data.certificate_url;
                document.getElementById('surveySuccessTitle').textContent = certificateReady ? 'Your certificate is ready' : 'Thank you';
                document.getElementById('surveySuccessText').textContent = certificateReady ? 'Your post-event survey is complete. You can now download your certificate.' : response.data.message;
                document.getElementById('surveySuccessAction').innerHTML = certificateReady ? `<a class="btn btn-primary-custom px-4" href="${escapeSurveyHtml(response.data.certificate_url)}">Download Certificate</a>` : '<button type="button" class="btn btn-primary-custom px-4" data-bs-dismiss="modal">Continue</button>';
                syncCertificateAvailability();
            } catch (error) { toastr.error(error.response?.data?.message || 'Unable to submit the survey'); renderSurveyQuestion(); }
        });

        updateSurveyCards();
        openRequiredSurvey();
        loadSurveyState().catch(() => {});
        let surveyReverbConnected = false;
        function connectSurveyReverb() {
            if (surveyReverbConnected || !window.Echo) return;
            surveyReverbConnected = true;
            window.Echo.channel(surveyChannel).listen('.survey.availability-changed', async event => {
                await loadSurveyState();
                const survey = moduleSurveyState?.[event.survey_type];
                if (event.enabled && survey?.eligible && !survey.completed && survey.questions.length) openSurvey(event.survey_type);
            });
        }
        window.addEventListener('load', connectSurveyReverb);
        window.setTimeout(connectSurveyReverb, 1000);
        window.setInterval(() => loadSurveyState().catch(() => {}), 15000);

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
                    videoProgressCircle?.classList.remove('pending');
                    videoProgressCircle?.classList.add('done');
                    videoProgressStatus && (videoProgressStatus.textContent = 'Done');
                    if (data.certificate_unlocked) {
                        certificateDownloadLink?.classList.remove('d-none');
                        certificateLockedWrap?.classList.add('d-none');
                        certificateProgressCircle?.classList.remove('pending');
                        certificateProgressCircle?.classList.add('done');
                        certificateProgressText?.classList.remove('text-muted');
                        certificateProgressText?.classList.add('fw-bold', 'text-dark-green');
                        certificateProgressText && (certificateProgressText.textContent = 'Certificate');
                    }
                    overallProgressCircle?.classList.remove('pending');
                    overallProgressCircle?.classList.add('done');
                    overallProgressText && (overallProgressText.textContent = `Complete ${data.progress_percent || 100}%`);
                    overallProgressText?.classList.remove('text-muted');
                    overallProgressText?.classList.add('fw-bold', 'text-dark-green');
                    loadSurveyState().then(state => {
                        if (state.post.enabled && state.post.eligible && !state.post.completed && state.post.questions.length) openSurvey('post');
                    }).catch(() => {});
                })
                .catch(() => {
                    moduleVideoCompletionRequestSent = false;
                });
        }

        const certificateAvailability = document.getElementById('certificateAvailability');

        function syncCertificateAvailability() {
            if (!certificateAvailability?.dataset.statusUrl || document.hidden) {
                return;
            }

            fetch(certificateAvailability.dataset.statusUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                cache: 'no-store',
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Unable to check certificate availability');
                    }

                    return response.json();
                })
                .then(data => {
                    const enabled = Boolean(data.enabled);
                    const unlocked = Boolean(data.unlocked);
                    const enabledValue = `${enabled ? '1' : '0'}:${unlocked ? '1' : '0'}`;
                    if (certificateAvailability.dataset.certificateEnabled === enabledValue) {
                        return;
                    }

                    certificateAvailability.dataset.certificateEnabled = enabledValue;
                    const currentButton = document.getElementById('certificateDownloadLink');
                    const actionWrap = document.getElementById('certificateActionWrap');
                    if (unlocked && data.url) {
                        const replacement = document.createElement('a');
                        replacement.id = 'certificateDownloadLink';
                        replacement.className = 'btn btn-primary-custom w-100 py-2';
                        replacement.textContent = 'Download Certificate';
                        replacement.href = data.url;
                        if (currentButton) currentButton.replaceWith(replacement);
                        else actionWrap?.append(replacement);
                        actionWrap?.classList.add('mb-4');
                    } else {
                        currentButton?.remove();
                        actionWrap?.classList.remove('mb-4');
                    }

                    const progressCircle = document.getElementById('certificateProgressCircle');
                    const progressText = document.getElementById('certificateProgressText');
                    progressCircle?.classList.toggle('done', unlocked);
                    progressCircle?.classList.toggle('pending', !unlocked);
                    progressText?.classList.toggle('fw-bold', unlocked);
                    progressText?.classList.toggle('text-dark-green', unlocked);
                    progressText?.classList.toggle('text-muted', !unlocked);

                    if (progressText) {
                        progressText.textContent = !enabled ? 'Certificate not released' : (unlocked ? 'Certificate' : 'Certificate pending');
                    }
                })
                .catch(() => {});
        }

        window.setInterval(syncCertificateAvailability, 3000);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                syncCertificateAvailability();
            }
        });

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
