<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Carbon\Carbon; // Make sure Carbon is imported

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Schedule the command to run at the start of each of your university's academic semesters.
        // The 'when' clause ensures it only runs in the specific month.
        // It runs on the 1st day of the month at 00:00 (midnight).

        // Semester 1: September 1st
        $schedule->command('semesters:create-new-for-users')
                 ->monthlyOn(1, '00:00')
                 ->when(fn () => now()->month === 9) // This condition makes it run ONLY in September
                 ->timezone('Europe/Amsterdam'); // Set to your server's timezone or desired timezone

        // Semester 2: January 1st
        $schedule->command('semesters:create-new-for-users')
                 ->monthlyOn(1, '00:00')
                 ->when(fn () => now()->month === 1) // This condition makes it run ONLY in January
                 ->timezone('Europe/Amsterdam');

        // Semester 3: May 1st
        $schedule->command('semesters:create-new-for-users')
                 ->monthlyOn(1, '00:00')
                 ->when(fn () => now()->month === 5) // This condition makes it run ONLY in May
                 ->timezone('Europe/Amsterdam');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
