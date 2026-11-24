<?php

use Carbon\CarbonImmutable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->timestamp('quote_issued_at')->nullable()->index();
        });

        DB::table('bookings')->where('status', 'Quoted')->whereNull('quote_issued_at')->orderBy('id')->chunkById(200, function ($bookings): void {
            foreach ($bookings as $booking) {
                $history = json_decode((string) $booking->status_history, true);
                $quotedEntry = collect(is_array($history) ? $history : [])->last(fn (array $entry): bool => ($entry['status'] ?? null) === 'Quoted');
                $issuedAt = $quotedEntry['at'] ?? $booking->updated_at;

                DB::table('bookings')->where('id', $booking->id)->update([
                    'quote_issued_at' => CarbonImmutable::parse($issuedAt)->utc()->format('Y-m-d H:i:s'),
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropIndex(['quote_issued_at']);
            $table->dropColumn('quote_issued_at');
        });
    }
};
