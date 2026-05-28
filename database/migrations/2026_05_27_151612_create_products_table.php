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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique()->nullable();        // auto-generated if blank
            $table->string('category');                        // yoghurt|drink|snack|packaging|others
            $table->string('size_volume')->nullable();         // e.g. 250ml, 1L
            $table->string('packaging_type')->nullable();      // bottle|sachet|carton|cup
            $table->string('unit');                            // piece|pack|carton|litre
            $table->decimal('base_price', 12, 2)->default(0); // standard selling price
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
