<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyNewBreakColumnsInAttendanceRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendance_requests', function (Blueprint $table) {
            $table->time('new_break_in')->nullable()->change();
            $table->time('new_break_out')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendance_requests', function (Blueprint $table) {
            $table->time('new_break_in')->nullable(false)->change();
            $table->time('new_break_out')->nullable(false)->change();
        });
    }
}
