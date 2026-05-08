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
        Schema::create('form_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('checklist_forms')->onDelete('cascade');
            $table->foreignId('section_id')->nullable()->constrained('form_sections')->onDelete('set null');
            $table->string('label');
            $table->enum('field_type', ['checkbox', 'radio', 'dropdown', 'text', 'number', 'textarea', 'signal', 'photo']);
            $table->json('options')->nullable(); // ["Online","Offline","Maintenance"]
            $table->boolean('is_required')->default(false);
            $table->integer('order_index')->default(0);
            $table->string('placeholder')->nullable();
            $table->string('helper_text')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_items');
    }
};
