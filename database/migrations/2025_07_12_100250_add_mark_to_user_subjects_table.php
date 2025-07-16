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
        Schema::table('user_subjects', function (Blueprint $table) {
            // Add 'mark' column if it doesn't exist
            if (!Schema::hasColumn('user_subjects', 'mark')) {
                $table->float('mark')->default(0)->after('subjectID'); // Place it after subjectID, or adjust as needed
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_subjects', function (Blueprint $table) {
            // Drop 'mark' column if it exists
            if (Schema::hasColumn('user_subjects', 'mark')) {
                $table->dropColumn('mark');
            }
        });
    }
};
