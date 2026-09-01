<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checklist_submissions', function (Blueprint $table) {
            $table->index(['submitted_by', 'status', 'submission_date'], 'submissions_user_status_date_index');
            $table->index(['status', 'submission_date'], 'submissions_status_date_index');
            $table->index(['form_id', 'submitted_by', 'status', 'submission_date'], 'submissions_form_user_status_date_index');
        });

        Schema::table('daily_activities', function (Blueprint $table) {
            $table->index(['user_id', 'activity_date', 'status'], 'activities_user_date_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('daily_activities', function (Blueprint $table) {
            $table->dropIndex('activities_user_date_status_index');
        });

        Schema::table('checklist_submissions', function (Blueprint $table) {
            $table->dropIndex('submissions_user_status_date_index');
            $table->dropIndex('submissions_status_date_index');
            $table->dropIndex('submissions_form_user_status_date_index');
        });
    }
};
