<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewBreaksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('new_breaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_request_id')->constrained('attendance_requests')->onDelete('cascade');
            $table->time('new_break_in')->nullable();
            $table->time('new_break_out')->nullable();
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
        Schema::dropIfExists('new_breaks');
    }
}
