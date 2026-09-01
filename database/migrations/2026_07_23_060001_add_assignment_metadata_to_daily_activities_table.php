<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('daily_activities')) {
            return;
        }

        Schema::table('daily_activities', function (Blueprint $table): void {
            if (! Schema::hasColumn('daily_activities', 'assigned_by')) {
                $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('daily_activities', 'assigned_at')) {
                $table->timestamp('assigned_at')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('daily_activities')) {
            return;
        }

        Schema::table('daily_activities', function (Blueprint $table): void {
            if (Schema::hasColumn('daily_activities', 'assigned_by')) {
                $table->dropForeign(['assigned_by']);
                $table->dropColumn('assigned_by');
            }
            if (Schema::hasColumn('daily_activities', 'assigned_at')) {
                $table->dropColumn('assigned_at');
            }
        });
    }
};
