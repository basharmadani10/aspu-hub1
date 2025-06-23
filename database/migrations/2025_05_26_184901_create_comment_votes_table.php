<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comment_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('comment_id')->constrained('comments')->onDelete('cascade');
            $table->enum('vote_type', ['up', 'down']);
            $table->timestamps();

            $table->unique(['user_id', 'comment_id']); // المستخدم يصوت مرة واحدة على نفس التعليق
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comment_votes');
    }
};
