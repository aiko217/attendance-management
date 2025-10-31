<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('attendance_id')->constrained('attendances')->onDelete('cascade');
            $table->string('approval_status')->default('pending');
            $table->date('request_date');
            $table->date('new_date');
            $table->time('new_clock_in');
            $table->time('new_clock_out');
            $table->time('new_break_in');
            $table->time('new_break_out')->nullable();
            $table->string('remarks');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendance_requests');
    }
}
