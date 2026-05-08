<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('checklist_forms', function (Blueprint $table) {
            $table->id();
        $table->string('title');
        $table->text('description')->nullable();
        $table->enum('schedule_type', ['daily', 'weekly', 'custom'])->default('daily');
        $table->json('schedule_days')->nullable(); // ["Mon","Wed","Fri"]
        $table->integer('schedule_interval')->nullable(); // untuk custom (setiap N hari)
        $table->date('start_date')->nullable();
        $table->date('end_date')->nullable();
        $table->boolean('is_active')->default(true);
        $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checklist_forms');
    }
};