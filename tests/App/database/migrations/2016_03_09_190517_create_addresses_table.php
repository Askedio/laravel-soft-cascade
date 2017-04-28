<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection()->getSchemaBuilder()->create('addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('profiles_id');
            $table->integer('languages_id');
            $table->string('city');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('profiles_id')->references('id')->on('profiles')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('languages_id')->references('id')->on('languages')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::connection()->getSchemaBuilder()->dropIfExists('addresses');
    }
}
