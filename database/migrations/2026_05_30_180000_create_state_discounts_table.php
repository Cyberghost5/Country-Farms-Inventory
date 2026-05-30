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
        Schema::dropIfExists('distributor_discounts');

        Schema::create('state_discounts', function (Blueprint $table) {
            $table->id();
            $table->string('state'); // State name matching users.state (e.g. 'Lagos')
            $table->string('type')->default('percentage'); // percentage|fixed
            $table->decimal('value', 8, 2)->default(0);
            $table->string('applies_to')->default('all'); // all|category|product
            $table->string('applies_value')->nullable(); // category name or product_id
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('state_discounts');

        Schema::create('distributor_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distributor_id')->constrained('users')->cascadeOnDelete();
            $table->string('type')->default('percentage');
            $table->decimal('value', 8, 2)->default(0);
            $table->string('applies_to')->default('all');
            $table->string('applies_value')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }
};
