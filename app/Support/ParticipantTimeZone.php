<?php

namespace App\Support;

use Carbon\CarbonImmutable;

final class ParticipantTimeZone
{
    private const COUNTRY_ZONES = [
        'singapore' => ['Asia/Singapore', 'SGT'],
        'malaysia' => ['Asia/Kuala_Lumpur', 'MYT'],
        'philippines' => ['Asia/Manila', 'PHT'],
        'thailand' => ['Asia/Bangkok', 'ICT'],
        'vietnam' => ['Asia/Ho_Chi_Minh', 'ICT'],
    ];

    private const DEFAULT_ZONE = ['Asia/Kolkata', 'IST'];

    public static function forCountry(?string $country): array
    {
        $key = strtolower(trim((string) $country));

        return self::COUNTRY_ZONES[$key] ?? self::DEFAULT_ZONE;
    }

    public static function eventDetails(?string $startAt, ?string $endAt, ?string $country): array
    {
        [$timeZone, $timeZoneLabel] = self::forCountry($country);
        $start = $startAt ? CarbonImmutable::parse($startAt, 'UTC')->setTimezone($timeZone) : null;
        $end = $endAt ? CarbonImmutable::parse($endAt, 'UTC')->setTimezone($timeZone) : null;

        return [
            'start' => $start,
            'end' => $end,
            'time_zone' => $timeZone,
            'time_zone_label' => $timeZoneLabel,
            'date' => $start?->format('d F Y'),
            'date_short' => $start?->format('d M Y'),
            'day' => $start?->format('l'),
            'time' => self::formatRange($start, $end, $timeZoneLabel),
        ];
    }

    public static function seaSummary(?string $startAt, ?string $endAt): ?string
    {
        if (! $startAt || ! $endAt) {
            return null;
        }

        $india = self::eventDetails($startAt, $endAt, null)['time'];
        $utcPlusEight = self::eventDetails($startAt, $endAt, 'Singapore');
        $utcPlusSeven = self::eventDetails($startAt, $endAt, 'Thailand');

        return $india
            .' · '.self::formatRange($utcPlusEight['start'], $utcPlusEight['end'], 'SG/MY/PH')
            .' · '.self::formatRange($utcPlusSeven['start'], $utcPlusSeven['end'], 'TH/VN');
    }

    public static function seaLines(?string $startAt, ?string $endAt): array
    {
        if (! $startAt || ! $endAt) {
            return [];
        }

        $india = self::eventDetails($startAt, $endAt, null);
        $utcPlusEight = self::eventDetails($startAt, $endAt, 'Singapore');
        $utcPlusSeven = self::eventDetails($startAt, $endAt, 'Thailand');

        return [
            self::formatRange($india['start'], $india['end'], 'IST'),
            self::formatRange($utcPlusEight['start'], $utcPlusEight['end'], 'SG/MY/PH'),
            self::formatRange($utcPlusSeven['start'], $utcPlusSeven['end'], 'TH/VN'),
        ];
    }

    private static function formatRange(?CarbonImmutable $start, ?CarbonImmutable $end, string $label): ?string
    {
        if (! $start) {
            return null;
        }

        if (! $end) {
            return $start->format('g:i A').' '.$label;
        }

        return $start->format('g:i A').' - '.$end->format('g:i A').' '.$label;
    }
}
