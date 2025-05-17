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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('hour_count')->nullable();
            $table->text('Description')->nullable();
            $table->float('paraticalMark')->nullable();
            $table->float('abstractMark')->nullable();
           $table->unsignedBigInteger('SpecializationID');
            $table->foreign('SpecializationID')->references('SpecializationID')->on('specializations')->onDelete('cascade');
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
        Schema::dropIfExists('subjects');
    }
};
