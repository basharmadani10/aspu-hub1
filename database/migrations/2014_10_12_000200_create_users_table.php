<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('first_name');
            $table->string('last_name');
            $table->date('birth_date')->nullable();
            $table->string('gender')->nullable();
            $table->string('image')->nullable();
            $table->text('bio')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->integer('email_verification_code')->nullable();
            $table->timestamp('email_verification_expires_at')->nullable();

            $table->string('password');
            $table->string('phone_number')->nullable();
            $table->string('country')->nullable();
            $table->string('current_location')->nullable();
            $table->unsignedBigInteger('roleID');
            $table->boolean('is_blocked')->default(false);
            $table->timestamps();

            // Uncomment this line if you have a roles table and want to set up a foreign key
            // $table->foreign('roleID')->references('roleID')->on('roles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
