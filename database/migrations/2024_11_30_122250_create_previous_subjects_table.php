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
        Schema::create('previous_subjects', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('subjectID');
            $table->foreign('subjectID')->references('id')->on('subjects')->onDelete('cascade');
            $table->unsignedBigInteger('PreviousSubjectID');
            $table->foreign('PreviousSubjectID')->references('id')->on('subjects')->onDelete('cascade');
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
        Schema::dropIfExists('previous_subjects');
    }
};
