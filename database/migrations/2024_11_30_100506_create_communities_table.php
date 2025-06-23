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
        Schema::create('communities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image')->nullable();
            $table->string('cover_image')->nullable();
            $table->integer('subscriber_count')->default(0);
            $table->timestamps();
        });



        DB::table('communities')->insert([
            [
                'id' => 1,
                'name' => 'global information technology'
            ],
            [
                'id' => 2,
                'name' => 'software'
            ],
            [
                'id' => 3,
                'name' => 'networking'
            ],
            [
                'id' => 4,
                'name' => 'ai'
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
        Schema::dropIfExists('communities');
    }
};
