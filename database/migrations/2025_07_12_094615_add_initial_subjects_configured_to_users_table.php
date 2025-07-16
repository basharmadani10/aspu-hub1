<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInitialSubjectsConfiguredToUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add 'initial_subjects_configured' column if it doesn't exist
            if (!Schema::hasColumn('users', 'initial_subjects_configured')) {
                $table->boolean('initial_subjects_configured')
                      ->default(false)
                      ->after('number_of_completed_hours')
                      ->comment('Indicates if the user has completed the initial setup of past subjects.');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop 'initial_subjects_configured' column if it exists
            if (Schema::hasColumn('users', 'initial_subjects_configured')) {
                $table->dropColumn('initial_subjects_configured');
            }
        });
    }
}
