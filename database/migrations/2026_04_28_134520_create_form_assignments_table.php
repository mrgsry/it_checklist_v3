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
        Schema::create('form_assignments', function (Blueprint $table) {
           $table->id();
        $table->foreignId('form_id')->constrained('checklist_forms')->onDelete('cascade');
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        $table->timestamp('assigned_at')->useCurrent();
        $table->timestamps();
        $table->unique(['form_id', 'user_id']); // tidak bisa assign user yang sama 2x
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_assignments');
    }
};