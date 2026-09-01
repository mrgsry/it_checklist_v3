<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_activities', function (Blueprint $table) {
            $table->string('type')->default('daily_activity')->after('user_id')->index();
            $table->string('category')->default('Bug / Error')->after('activity');
            $table->foreignId('submission_id')->nullable()->after('category')->constrained('checklist_submissions')->nullOnDelete();
            $table->string('ticket_item')->nullable()->after('submission_id');
            $table->string('ticket_number')->nullable()->after('ticket_item');
            $table->string('ticket_url')->nullable()->after('ticket_number');
            $table->unique(['submission_id', 'ticket_item']);
        });
    }

    public function down(): void
    {
        Schema::table('daily_activities', function (Blueprint $table) {
            $table->dropUnique('daily_activities_submission_id_ticket_item_unique');
            $table->dropForeign(['submission_id']);
            $table->dropColumn(['type', 'category', 'submission_id', 'ticket_item', 'ticket_number', 'ticket_url']);
        });
    }
};