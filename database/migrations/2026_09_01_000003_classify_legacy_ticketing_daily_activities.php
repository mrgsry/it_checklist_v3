<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('daily_activities')
            ->where('type', 'daily_activity')
            ->where('activity', 'like', 'Selesaikan Ticket #%')
            ->update(['type' => 'ticketing']);
    }

    public function down(): void
    {
        DB::table('daily_activities')
            ->where('type', 'ticketing')
            ->whereNull('submission_id')
            ->whereNull('ticket_item')
            ->whereNull('ticket_number')
            ->where('activity', 'like', 'Selesaikan Ticket #%')
            ->update(['type' => 'daily_activity']);
    }
};