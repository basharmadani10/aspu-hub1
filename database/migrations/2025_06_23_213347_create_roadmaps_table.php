<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoadmapsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roadmaps', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('specialization_id')->nullable();
            $table->timestamps();
            $table->foreign('specialization_id')->references('SpecializationID')->on('specializations')->onDelete('set null');
        });

        Schema::create('roadmap_subjects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('roadmap_id');
            $table->unsignedBigInteger('subject_id');
            $table->timestamps();
            $table->foreign('roadmap_id')->references('id')->on('roadmaps')->onDelete('cascade');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
            $table->unique(['roadmap_id', 'subject_id']);
        });
    }
    public function down()
    {
        Schema::dropIfExists('roadmap_subjects');
        Schema::dropIfExists('roadmaps');
    }
}
