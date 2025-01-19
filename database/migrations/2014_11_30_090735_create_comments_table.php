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
  // database/migrations/xxxx_xx_xx_create_comments_table.php
public function up()
{
    Schema::create('comments', function (Blueprint $table) {
        $table->id();
        $table->text('content');
        $table->unsignedBigInteger('post_id');
        $table->unsignedBigInteger('user_id');
        $table->integer('positive_votes')->default(0);
        $table->integer('negative_votes')->default(0);
        $table->unsignedBigInteger('parent_comment_id')->nullable();
        $table->timestamps();

      #  $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        $table->foreign('parent_comment_id')->references('id')->on('comments')->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comments');
    }
};
