<?php

namespace Tests\Unit;

use App\Support\ParticipantTimeZone;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ParticipantTimeZoneTest extends TestCase
{
    public static function countryTimes(): array
    {
        return [
            'Singapore' => ['Singapore', '6:30 PM - 8:00 PM SGT'],
            'Malaysia' => ['Malaysia', '6:30 PM - 8:00 PM MYT'],
            'Philippines' => ['Philippines', '6:30 PM - 8:00 PM PHT'],
            'Myanmar' => ['Myanmar', '5:00 PM - 6:30 PM MMT'],
            'Thailand' => ['Thailand', '5:30 PM - 7:00 PM ICT'],
            'Vietnam' => ['Vietnam', '5:30 PM - 7:00 PM ICT'],
            'fallback India' => ['India', '4:00 PM - 5:30 PM IST'],
        ];
    }

    #[DataProvider('countryTimes')]
    public function test_event_time_is_localized_for_participant_country(string $country, string $expected): void
    {
        $details = ParticipantTimeZone::eventDetails(
            '2026-11-20 10:30:00',
            '2026-11-20 12:00:00',
            $country,
        );

        $this->assertSame($expected, $details['time']);
    }

    public function test_sea_summary_contains_all_requested_country_groups(): void
    {
        $summary = ParticipantTimeZone::seaSummary('2026-11-20 10:30:00', '2026-11-20 12:00:00');

        $this->assertSame(
            '4:00 PM - 5:30 PM IST · 5:00 PM - 6:30 PM Myanmar · 6:30 PM - 8:00 PM SG/MY/PH · 5:30 PM - 7:00 PM TH/VN',
            $summary,
        );
    }

    public function test_sea_lines_are_in_requested_vertical_order(): void
    {
        $this->assertSame([
            '4:00 PM - 5:30 PM IST',
            '5:00 PM - 6:30 PM Myanmar',
            '6:30 PM - 8:00 PM SG/MY/PH',
            '5:30 PM - 7:00 PM TH/VN',
        ], ParticipantTimeZone::seaLines('2026-11-20 10:30:00', '2026-11-20 12:00:00'));
    }
}
