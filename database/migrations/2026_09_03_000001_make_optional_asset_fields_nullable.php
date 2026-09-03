<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table): void {
            $table->string('brand', 100)->nullable()->change();
            $table->string('type', 100)->nullable()->change();
            $table->string('item_code', 100)->nullable()->change();
            $table->string('inventory_number', 100)->nullable()->change();
            $table->string('serial_number', 150)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table): void {
            $table->string('brand', 100)->nullable(false)->change();
            $table->string('type', 100)->nullable(false)->change();
            $table->string('item_code', 100)->nullable(false)->change();
            $table->string('inventory_number', 100)->nullable(false)->change();
            $table->string('serial_number', 150)->nullable(false)->change();
        });
    }
};