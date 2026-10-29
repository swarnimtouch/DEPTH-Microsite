<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Module extends Model
{
    protected $fillable = [
        'title',
        'venue',
        'course_id',
        'slug',
        'doctor_id',
        'speaker_name',
        'speaker_designation',
        'speaker_hospital',
        'speakers',
        'agenda',
        'brands',
        'archive_segments',
        'duration',
        'cme_credit',
        'thumbnail',
        'certificate_template',
        'certificate_name_x',
        'certificate_name_y',
        'certificate_font_size',
        'video_url',
        'join_url',
        'overview',
        'key_takeaways',
        'resources',
        'sort_order',
        'start_date',
        'end_date',
        'start_at',
        'end_at',
        'is_locked',
        'join_enabled',
        'certificate_enabled',
        'status',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
        'join_enabled' => 'boolean',
        'certificate_enabled' => 'boolean',
        'cme_credit' => 'decimal:1',
        'certificate_name_x' => 'decimal:2',
        'certificate_name_y' => 'decimal:2',
        'certificate_font_size' => 'integer',
        'speakers' => 'array',
        'agenda' => 'array',
        'brands' => 'array',
        'archive_segments' => 'array',
        'sort_order' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    protected $appends = [
        'start_at_utc_iso',
        'end_at_utc_iso',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function isAvailable(): bool
    {
        $now = now('UTC');
        $startAt = $this->utcDateTime('start_at');
        $endAt = $this->utcDateTime('end_at');

        return (!$startAt || $startAt->lte($now))
            && (!$endAt || $endAt->gte($now));
    }

    public function isJoinOpen(): bool
    {
        if ($this->join_enabled) {
            return true;
        }

        $startAt = $this->utcDateTime('start_at');

        return $startAt !== null && $startAt->lte(now('UTC'));
    }

    public function getDisplayDurationAttribute(): ?string
    {
        $startAt = $this->utcDateTime('start_at');
        $endAt = $this->utcDateTime('end_at');

        if ($startAt && $endAt && $endAt->gt($startAt)) {
            $minutes = (int) $startAt->diffInMinutes($endAt);

            return intdiv($minutes, 60) . ':' . str_pad((string) ($minutes % 60), 2, '0', STR_PAD_LEFT);
        }

        return $this->duration;
    }

    public function getStartAtUtcIsoAttribute(): ?string
    {
        return $this->utcDateTime('start_at')?->toIso8601String();
    }

    public function getEndAtUtcIsoAttribute(): ?string
    {
        return $this->utcDateTime('end_at')?->toIso8601String();
    }

    private function utcDateTime(string $attribute): ?CarbonImmutable
    {
        $value = $this->getRawOriginal($attribute);

        return $value ? CarbonImmutable::parse($value, 'UTC') : null;
    }

    public function getYoutubeEmbedUrlAttribute(): ?string
    {
        return static::youtubeEmbedUrl($this->video_url);
    }

    public static function youtubeEmbedUrl(?string $url, ?string $startTime = null): ?string
    {
        if (!$url) {
            return null;
        }

        $url = trim($url);
        $videoId = null;
        if (preg_match('/youtu\.be\/([A-Za-z0-9_-]+)/', $url, $matches)) {
            $videoId = $matches[1];
        }

        if (!$videoId && preg_match('/youtube\.com\/embed\/([A-Za-z0-9_-]+)/', $url, $matches)) {
            $videoId = $matches[1];
        }

        if (!$videoId && preg_match('/youtube\.com\/(?:live|shorts)\/([A-Za-z0-9_-]+)/', $url, $matches)) {
            $videoId = $matches[1];
        }

        if (!$videoId) {
            $query = parse_url($url, PHP_URL_QUERY);
            parse_str($query ?: '', $params);
            $videoId = $params['v'] ?? null;
        }

        if (!$videoId) {
            return null;
        }

        $embedUrl = 'https://www.youtube.com/embed/' . $videoId;
        $startSeconds = static::timeToSeconds($startTime);
        if ($startSeconds > 0) {
            $embedUrl .= '?start=' . $startSeconds;
        }

        return $embedUrl;
    }

    public static function timeToSeconds(?string $time): int
    {
        $time = trim((string) $time);
        if ($time === '') {
            return 0;
        }

        if (ctype_digit($time)) {
            return (int) $time;
        }

        $parts = array_map('intval', explode(':', $time));
        if (count($parts) === 2) {
            return ($parts[0] * 60) + $parts[1];
        }

        if (count($parts) === 3) {
            return ($parts[0] * 3600) + ($parts[1] * 60) + $parts[2];
        }

        return 0;
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('sort_order')->orderBy('id');
    }

    public function activeQuestions(): HasMany
    {
        return $this->questions()->where('status', 'active');
    }

    public function progresses(): HasMany
    {
        return $this->hasMany(ModuleUserProgress::class);
    }

    public function viewSessions(): HasMany
    {
        return $this->hasMany(ModuleViewSession::class);
    }

    public function resourceDocuments(): HasMany
    {
        return $this->hasMany(ModuleResource::class)->orderBy('sort_order')->orderBy('id');
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        while (
            static::where('slug', $slug)
                ->when($ignoreId, fn($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter++;
        }

        return $slug;
    }
}
