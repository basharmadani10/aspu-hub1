<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_semesters', function (Blueprint $table) {
            if (!Schema::hasColumn('user_semesters', 'has_registered_subjects')) {
                $table->boolean('has_registered_subjects')
                      ->default(false)
                      ->after('userID');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_semesters', function (Blueprint $table) {
            // Drop 'has_registered_subjects' column if it exists
            if (Schema::hasColumn('user_semesters', 'has_registered_subjects')) {
                $table->dropColumn('has_registered_subjects');
            }
        });
    }
};
