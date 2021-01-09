<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePhonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('phones', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('vc_phone');
            $table->string('vc_fio')->nullable();
            $table->date('dt_born')->nullable();
            $table->tinyInteger('sex_id')->nullable();
            $table->string('vc_region')->nullable();
            $table->string('vc_city')->nullable();
            $table->text('tx_location')->nullable();
            $table->string('vc_email')->nullable();
            $table->date('dt_rec')->nullable();
            $table->string('vc_link')->nullable();
            $table->text('tx_rem')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('phones');
    }
}
