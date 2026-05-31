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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('state')->nullable()->after('distributor_id');
        });

        Schema::table('dispatches', function (Blueprint $table) {
            $table->string('state')->nullable()->after('distributor_id');
        });

        // Populate existing orders & dispatches with distributor's current state
        $orders = DB::table('orders')->get();
        foreach ($orders as $o) {
            $u = DB::table('users')->where('id', $o->distributor_id)->first();
            if ($u) {
                DB::table('orders')->where('id', $o->id)->update(['state' => $u->state]);
            }
        }

        $dispatches = DB::table('dispatches')->get();
        foreach ($dispatches as $d) {
            $u = DB::table('users')->where('id', $d->distributor_id)->first();
            if ($u) {
                DB::table('dispatches')->where('id', $d->id)->update(['state' => $u->state]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('state');
        });

        Schema::table('dispatches', function (Blueprint $table) {
            $table->dropColumn('state');
        });
    }
};
