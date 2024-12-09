<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGroupIdToBookingUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booking_users', function (Blueprint $table) {
            $table->unsignedBigInteger('group_id')->nullable()->after('id');

            // Si necesitas establecer una clave foránea con otra tabla, puedes utilizar lo siguiente
            // $table->foreign('group_id')->references('id')->on('groups');
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
            // Si has definido la clave foránea, primero necesitarás soltarla
            // $table->dropForeign(['group_id']);
            $table->dropColumn('group_id');
        });
    }
}
