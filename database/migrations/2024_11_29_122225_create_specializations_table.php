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
        Schema::create('specializations', function (Blueprint $table) {

            $table->id('SpecializationID');

            $table->string('name')->unique();
            $table->string('description');
            $table->boolean('is_for_university')->default(true);
            $table->timestamps();

        });


        DB::table('specializations')->insert([
            [
                'SpecializationID' => 1,
                'name' => 'global information technology',
                'description' => 'Comprehensive IT education covering multiple disciplines'
            ],
            [
                'SpecializationID' => 2,
                'name' => 'software',
                'description' => 'Software development and engineering focus'
            ],
            [
                'SpecializationID' => 3,
                'name' => 'networking',
                'description' => 'Network infrastructure and security'
            ],
            [
                'SpecializationID' => 4,
                'name' => 'ai',
                'description' => 'Artificial intelligence and machine learning'
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('specializations');
    }
};
