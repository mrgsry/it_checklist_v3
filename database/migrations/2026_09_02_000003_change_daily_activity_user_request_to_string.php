<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_activities', function (Blueprint $table): void {
            $table->string('user_request')->nullable()->after('submission_id');
        });

        DB::table('daily_activities')
            ->whereNotNull('user_request_id')
            ->whereNull('daily_activities.user_request')
            ->update(['user_request' => DB::raw('(select requester from user_requests where user_requests.id = daily_activities.user_request_id)')]);

        Schema::table('daily_activities', function (Blueprint $table): void {
            $table->dropForeign(['user_request_id']);
            $table->dropColumn('user_request_id');
        });
    }

    public function down(): void
    {
        Schema::table('daily_activities', function (Blueprint $table): void {
            $table->foreignId('user_request_id')->nullable()->after('submission_id')->constrained('user_requests')->nullOnDelete();
        });

        DB::table('daily_activities')
            ->whereNotNull('user_request')
            ->whereNull('daily_activities.user_request_id')
            ->update(['user_request_id' => DB::raw('(select id from user_requests where user_requests.requester = daily_activities.user_request limit 1)')]);

        Schema::table('daily_activities', function (Blueprint $table): void {
            $table->dropColumn('user_request');
        });
    }
};