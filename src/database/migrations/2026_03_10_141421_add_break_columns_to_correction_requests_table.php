<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('correction_requests', function (Blueprint $table) {

            $table->dateTime('requested_break1_start')->nullable()->after('requested_clock_out');
            $table->dateTime('requested_break1_end')->nullable()->after('requested_break1_start');

            $table->dateTime('requested_break2_start')->nullable()->after('requested_break1_end');
            $table->dateTime('requested_break2_end')->nullable()->after('requested_break2_start');
        });
    }

    public function down(): void
    {
        Schema::table('correction_requests', function (Blueprint $table) {

            $table->dropColumn([
                'requested_break1_start',
                'requested_break1_end',
                'requested_break2_start',
                'requested_break2_end'
            ]);
        });
    }
};
