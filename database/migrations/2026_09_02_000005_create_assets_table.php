<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('asset_category_id')
                ->constrained('asset_categories')
                ->restrictOnDelete();
            $table->string('name');
            $table->unsignedSmallInteger('purchase_year')->index();
            $table->string('brand', 100)->nullable();
            $table->string('type', 100)->nullable();
            $table->string('item_code', 100)->nullable()->unique();
            $table->string('inventory_number', 100)->nullable()->unique();
            $table->string('serial_number', 150)->nullable()->unique();
            $table->unsignedInteger('quantity')->default(1);
            $table->string('location')->index();
            $table->timestamps();

            $table->index(['asset_category_id', 'purchase_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
