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
// database/migrations/xxxx_xx_xx_create_favorite_posts_table.php
public function up()
{
    Schema::create('favorite_posts', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id');
       # $table->unsignedBigInteger('post_id');
        $table->timestamps();

        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
      #  $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
    });
}



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('favorite_posts');
    }
};
