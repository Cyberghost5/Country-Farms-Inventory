<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_operating_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('state');
            $table->string('lga')->nullable();
            $table->timestamps();
        });

        // Copy legacy data
        $users = DB::table('users')->whereNotNull('state')->where('state', '!=', '')->get();
        foreach ($users as $u) {
            DB::table('user_operating_areas')->insert([
                'user_id' => $u->id,
                'state' => $u->state,
                'lga' => $u->lga ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_operating_areas');
    }
};
