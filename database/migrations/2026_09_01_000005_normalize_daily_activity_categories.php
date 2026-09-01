<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $mapping = [
            'Jaringan' => 'Network/System',
            'Hardware' => 'Peripherals',
            'Aplikasi' => 'App Request',
            'Software' => 'App Request',
            'Infrastruktur' => 'Network/System',
            'Keamanan' => 'Bug / Error',
            'Lainnya' => 'Bug / Error',
        ];

        foreach ($mapping as $old => $new) {
            DB::table('daily_activities')->where('category', $old)->update(['category' => $new]);
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE daily_activities ALTER COLUMN category SET DEFAULT 'Bug / Error'");
        }
    }

    public function down(): void
    {
        // Category values are intentionally not reversed: the old categories were
        // not a one-to-one mapping and reversing them would corrupt new records.
    }
};