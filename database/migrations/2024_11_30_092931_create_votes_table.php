<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
   // database/migrations/xxxx_xx_xx_create_votes_table.php
public function up()
{
    Schema::create('votes', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('location_id');
        $table->unsignedBigInteger('user_id');
        $table->boolean('type');
        $table->timestamps();

      #  $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('votes');
    }
};
