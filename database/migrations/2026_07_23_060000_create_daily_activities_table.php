<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('daily_activities')) {
            return;
        }

        Schema::create('daily_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable()->index();
            $table->date('activity_date')->index();
            $table->string('activity');
            $table->enum('status', ['completed', 'in_progress', 'blocked'])->default('completed')->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'activity_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_activities');
    }
};
