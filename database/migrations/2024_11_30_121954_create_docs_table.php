<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('docs', function (Blueprint $table) {
            $table->id('DocID');
            $table->unsignedBigInteger('docs_type_id');
            $table->string('doc_url');
            $table->timestamps();

            $table->foreign('docs_type_id')
                  ->references('id')
                  ->on('docs_types')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('docs');
    }
};
