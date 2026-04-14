<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('correction_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('attendance_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->dateTime('requested_clock_in')->nullable();
            $table->dateTime('requested_clock_out')->nullable();
            $table->dateTime('requested_break1_start')->nullable();
            $table->dateTime('requested_break1_end')->nullable();
            $table->dateTime('requested_break2_start')->nullable();
            $table->dateTime('requested_break2_end')->nullable();
            $table->text('requested_note')->nullable();
            $table->string('status')->default('pending');
            $table->text('admin_comment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correction_requests');
    }
};
