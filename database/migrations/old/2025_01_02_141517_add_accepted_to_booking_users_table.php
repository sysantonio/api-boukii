<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAcceptedToBookingUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booking_users', function (Blueprint $table) {
            $table->boolean('accepted')->default(true)->after('status'); // Cambia 'status' por el campo anterior en tu tabla
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('booking_users', function (Blueprint $table) {
            $table->dropColumn('accepted');
        });
    }
}
