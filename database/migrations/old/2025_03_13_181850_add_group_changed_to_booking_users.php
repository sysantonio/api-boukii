<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('booking_users', function (Blueprint $table) {
            $table->boolean('group_changed')->default(false)->after('accepted');
        });
    }

    public function down()
    {
        Schema::table('booking_users', function (Blueprint $table) {
            $table->dropColumn('group_changed');
        });
    }

};
