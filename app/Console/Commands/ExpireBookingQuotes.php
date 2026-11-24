<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Notifications\BookingUpdate;
use Carbon\CarbonImmutable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Throwable;

#[Signature('bookings:expire-quotes')]
#[Description('Expire booking quotations after 30 days')]
class ExpireBookingQuotes extends Command
{
    public function handle(): int
    {
        $now = CarbonImmutable::now('UTC');
        $expiryCutoff = $now->subDays(30);
        $expiredCount = 0;

        Booking::query()
            ->where('status', 'Quoted')
            ->whereNotNull('quote_issued_at')
            ->where('quote_issued_at', '<=', $expiryCutoff->format('Y-m-d H:i:s'))
            ->orderBy('id')
            ->chunkById(100, function ($bookings) use ($now, $expiryCutoff, &$expiredCount): void {
                foreach ($bookings as $bookingCandidate) {
                    $expiredBooking = DB::transaction(function () use ($bookingCandidate, $now, $expiryCutoff): ?Booking {
                        $booking = Booking::query()->lockForUpdate()->find($bookingCandidate->getKey());

                        if (! $booking || $booking->status !== 'Quoted' || ! $booking->quote_issued_at || $booking->quote_issued_at->greaterThan($expiryCutoff)) {
                            return null;
                        }

                        $history = $booking->status_history ?? [];
                        $history[] = ['status' => 'Expired', 'at' => $now->toIso8601String()];
                        $booking->forceFill(['status' => 'Expired', 'status_history' => $history])->save();

                        return $booking;
                    });

                    if (! $expiredBooking) {
                        continue;
                    }

                    $expiredCount++;

                    if ($expiredBooking->email) {
                        try {
                            $statusUrl = URL::temporarySignedRoute('booking.confirmation', $now->addDays(90), ['reference' => $expiredBooking->reference]);
                            Notification::route('mail', $expiredBooking->email)->notify(new BookingUpdate($expiredBooking, false, $statusUrl));
                        } catch (Throwable $exception) {
                            report($exception);
                        }
                    }
                }
            });

        $this->info("Expired {$expiredCount} quotation(s).");

        return self::SUCCESS;
    }
}
