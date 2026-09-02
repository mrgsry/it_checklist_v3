<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('submission_id')->nullable()->constrained('checklist_submissions')->nullOnDelete();
            $table->string('ticket_item');
            $table->string('requester');
            $table->string('departement')->nullable();
            $table->string('contact')->nullable();
            $table->string('email')->nullable();
            $table->text('detail')->nullable();
            $table->string('location')->nullable();
            $table->timestamps();

            $table->unique(['submission_id', 'ticket_item']);
        });

        Schema::table('daily_activities', function (Blueprint $table): void {
            $table->foreignId('user_request_id')->nullable()->after('submission_id')->constrained('user_requests')->nullOnDelete();
        });

        DB::table('checklist_submissions')
            ->select(['id', 'ticketing_data'])
            ->whereNotNull('ticketing_data')
            ->orderBy('id')
            ->get()
            ->each(function (object $submission): void {
                foreach ((json_decode($submission->ticketing_data, true) ?: []) as $item => $ticket) {
                    if (! is_array($ticket) || ! filled($ticket['user'] ?? null)) {
                        continue;
                    }

                    $requestId = DB::table('user_requests')->insertGetId([
                        'submission_id' => $submission->id,
                        'ticket_item' => $item,
                        'requester' => $ticket['user'],
                        'departement' => $ticket['departement'] ?? null,
                        'contact' => $ticket['contact'] ?? null,
                        'email' => $ticket['email'] ?? null,
                        'detail' => $ticket['detail'] ?? null,
                        'location' => $ticket['location'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('daily_activities')
                        ->where('submission_id', $submission->id)
                        ->where('ticket_item', $item)
                        ->update(['user_request_id' => $requestId]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('daily_activities', function (Blueprint $table): void {
            $table->dropForeign(['user_request_id']);
            $table->dropColumn('user_request_id');
        });

        Schema::dropIfExists('user_requests');
    }
};