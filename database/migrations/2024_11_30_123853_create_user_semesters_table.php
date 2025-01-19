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
    public function up()
    {
        Schema::create('user_semesters', function (Blueprint $table) {

            $table->id();
            $table->unsignedBigInteger('userID');
            $table->unsignedBigInteger('SpecializationID');

            $table->date('start_date');
            $table->foreign(columns: 'userID')->references('id')->on('users')->onDelete('cascade');
            $table->foreign(columns: 'SpecializationID')->references('SpecializationID')->on('specializations')->onDelete('cascade');
            $table->date('end_date');
            $table->integer('semester_number');
            $table->integer('semester_hours');
            $table->float('year_degree');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_semesters');
    }
};
