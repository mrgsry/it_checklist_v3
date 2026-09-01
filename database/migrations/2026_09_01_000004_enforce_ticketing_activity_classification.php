<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('daily_activities')
            ->where('activity', 'like', 'Selesaikan Ticket #%')
            ->update(['type' => 'ticketing']);

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared('DROP TRIGGER IF EXISTS daily_activities_classify_ticketing_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS daily_activities_classify_ticketing_update');
        DB::unprepared("CREATE TRIGGER daily_activities_classify_ticketing_insert BEFORE INSERT ON daily_activities FOR EACH ROW SET NEW.type = IF(NEW.activity LIKE 'Selesaikan Ticket #%', 'ticketing', COALESCE(NEW.type, 'daily_activity'))");
        DB::unprepared("CREATE TRIGGER daily_activities_classify_ticketing_update BEFORE UPDATE ON daily_activities FOR EACH ROW SET NEW.type = IF(NEW.activity LIKE 'Selesaikan Ticket #%', 'ticketing', COALESCE(NEW.type, 'daily_activity'))");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::unprepared('DROP TRIGGER IF EXISTS daily_activities_classify_ticketing_insert');
            DB::unprepared('DROP TRIGGER IF EXISTS daily_activities_classify_ticketing_update');
        }
    }
};