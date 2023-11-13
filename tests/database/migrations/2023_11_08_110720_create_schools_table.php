<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchoolsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('description', 100);
            $table->text('contact_email')->nullable();
            $table->text('contact_phone')->nullable();
            $table->text('contact_telephone')->nullable();
            $table->text('contact_address')->nullable();
            $table->text('contact_cp')->nullable();
            $table->text('contact_city')->nullable();
            $table->string('contact_province', 100)->nullable();
            $table->string('contact_country', 100)->nullable();
            $table->string('fiscal_name', 100)->default('');
            $table->string('fiscal_id', 100)->default('');
            $table->string('fiscal_address', 100)->default('');
            $table->string('fiscal_cp', 100)->default('');
            $table->string('fiscal_city', 100)->default('');
            $table->string('fiscal_province', 100)->nullable();
            $table->string('fiscal_country', 100)->nullable();
            $table->string('iban', 100)->default('');
            $table->string('logo', 500)->default('');
            $table->string('slug', 100);
            $table->decimal('cancellation_insurance_percent', 5, 2)->default(10.00);
            $table->text('payrexx_instance')->nullable();
            $table->text('payrexx_key')->nullable();
            $table->string('conditions_url', 100)->default('');
            $table->decimal('bookings_comission_cash', 8, 2)->default(5.00);
            $table->decimal('bookings_comission_boukii_pay', 8, 2)->default(5.00);
            $table->decimal('bookings_comission_other', 8, 2)->default(5.00);
            $table->double('school_rate', 8, 2)->default(0.00)->index('school_rate_id');
            $table->boolean('has_ski')->default(0);
            $table->boolean('has_snowboard')->default(0);
            $table->boolean('has_telemark')->default(0);
            $table->boolean('has_rando')->default(0);
            $table->boolean('inscription')->default(0);
            $table->string('type', 100)->default('')->index('type_id');
            $table->boolean('active')->default(1);
            $table->json('settings');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schools');
    }
}
