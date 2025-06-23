<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DocsType;
use Illuminate\Support\Facades\DB; // Import DB facade

class DocsTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // For MySQL/MariaDB

        // Truncate the table (if you want to clear previous Arabic entries)
        DocsType::truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // For MySQL/MariaDB

        // Add "lecture"
        DocsType::firstOrCreate(
            ['name' => 'lecture'], // Changed to English singular
            ['description' => 'Lecture files for subjects'] // Update description to English
        );

        // Add "summary"
        DocsType::firstOrCreate(
            ['name' => 'summary'], // Changed to English singular
            ['description' => 'Summaries and notes for subjects'] // Update description to English
        );
    }
}
