<?php

namespace App;

use App\Models\Booking;
use App\Models\SiteSetting;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class BookingSchedule
{
    public const STATUSES = ['Pending', 'New', 'Contacted', 'Quoted', 'Confirmed', 'Scheduled', 'In Progress', 'Completed', 'Cancelled', 'Rejected'];

    public const DEFAULTS = [
        'enabled' => true,
        'weekdays' => [1, 2, 3, 4, 5, 6],
        'opening' => '08:00',
        'closing' => '17:00',
        'interval' => 60,
        'capacity' => 1,
        'notice' => 24,
        'blocked_dates' => [],
        'service_rules' => [],
    ];

    public function now(): CarbonImmutable
    {
        return CarbonImmutable::now('Africa/Nairobi');
    }

    public function settings(): array
    {
        $stored = json_decode(SiteSetting::query()->where('key', 'booking_schedule')->value('value') ?? '', true);

        return array_replace(self::DEFAULTS, is_array($stored) ? $stored : []);
    }

    public function lock(): void
    {
        DB::table('site_settings')->insertOrIgnore(['key' => 'booking_schedule_mutex', 'value' => '0']);
        DB::table('site_settings')->where('key', 'booking_schedule_mutex')->update(['value' => '0']);
    }

    public function availability(int $serviceId, CarbonImmutable $first, CarbonImmutable $last, ?int $excluding = null): array
    {
        $settings = $this->settings();
        $rules = $settings['service_rules'][$serviceId] ?? [];
        $settings = array_replace($settings, $rules);
        $interval = (int) $settings['interval'];
        $duration = (int) ($rules['duration'] ?? $interval);
        $now = $this->now();
        $earliest = $now->addHours((int) $settings['notice']);
        $bookings = Booking::query()->whereNotIn('status', ['Cancelled', 'Rejected'])
            ->where('preferred_date', '>=', $first->toDateString())
            ->where('preferred_date', '<', $last->addDay()->toDateString())
            ->whereNotNull('preferred_time')
            ->when($excluding, fn ($query) => $query->whereKeyNot($excluding))
            ->get(['preferred_date', 'preferred_time', 'scheduled_duration'])
            ->groupBy(fn (Booking $booking): string => $booking->preferred_date->toDateString());

        $result = [];
        for ($day = $first; $day->lte($last); $day = $day->addDay()) {
            $date = $day->toDateString();
            $result[$date] = [];
            if (! $settings['enabled'] || $day->gt($now->startOfDay()->addDays(120))
                || ! in_array($day->dayOfWeekIso, $settings['weekdays'], true) || in_array($date, $settings['blocked_dates'], true)) {
                continue;
            }

            for ($start = $this->minutes($settings['opening']); $start + $duration <= $this->minutes($settings['closing']); $start += $interval) {
                $time = sprintf('%02d:%02d', intdiv($start, 60), $start % 60);
                if ($day->setTimeFromTimeString($time)->lt($earliest)) {
                    continue;
                }

                $room = true;
                for ($minute = $start; $minute < $start + $duration; $minute++) {
                    $occupied = $bookings->get($date, collect())->filter(function (Booking $booking) use ($minute): bool {
                        if (! preg_match('/^\d{2}:\d{2}$/', $booking->preferred_time)) {
                            return false;
                        }
                        $bookedStart = $this->minutes($booking->preferred_time);

                        return $bookedStart <= $minute && $bookedStart + ($booking->scheduled_duration ?? 60) > $minute;
                    })->count();
                    if ($occupied >= $settings['capacity']) {
                        $room = false;
                        break;
                    }
                }
                if ($room) {
                    $result[$date][] = $time;
                }
            }
        }

        return $result;
    }

    public function duration(int $serviceId): int
    {
        $settings = $this->settings();

        return (int) ($settings['service_rules'][$serviceId]['duration'] ?? $settings['interval']);
    }

    private function minutes(string $time): int
    {
        [$hour, $minute] = explode(':', $time);

        return (int) $hour * 60 + (int) $minute;
    }
}
