<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\UserSemester;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateNewUserSemesters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'semesters:create-new-for-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates new UserSemester records for all active student users at the start of a new academic semester.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('Starting CreateNewUserSemesters command.');
        $this->info('Creating new UserSemester records for active users...');

        $currentYear = now()->year;

        // Define the current academic semester's details based on the current date
        $semesterDetails = null;

        // Assuming your semesters are:
        // Semester 1: September 1st - December 31st (4 months)
        // Semester 2: January 1st - April 30th (4 months)
        // Semester 3: May 1st - June 30th (2 months)


        if (now()->month === 7) { // If current month is July
            $semesterDetails = [
                'type' => 99, // Using a unique 'type' like 99 to signify a test semester
                'start_date' => Carbon::create($currentYear, 7, 1)->startOfDay(),
                'end_date' => Carbon::create($currentYear, 7, 31)->endOfDay(),
            ];
            $this->info('TEST MODE: Running for July (temporary semester).');
        }
        // --- END TEMPORARY CODE ---

        // Original logic for your defined academic semesters:
        // This 'elseif' ensures that the temporary July block takes precedence when present.
        elseif (now()->isBetween(Carbon::create($currentYear, 9, 1)->startOfDay(), Carbon::create($currentYear, 12, 31)->endOfDay())) {
            $semesterDetails = [
                'type' => 1,
                'start_date' => Carbon::create($currentYear, 9, 1)->startOfDay(),
                'end_date' => Carbon::create($currentYear, 12, 31)->endOfDay(),
            ];
        } elseif (now()->isBetween(Carbon::create($currentYear, 1, 1)->startOfDay(), Carbon::create($currentYear, 4, 30)->endOfDay())) {
            $semesterDetails = [
                'type' => 2,
                'start_date' => Carbon::create($currentYear, 1, 1)->startOfDay(),
                'end_date' => Carbon::create($currentYear, 4, 30)->endOfDay(),
            ];
        } elseif (now()->isBetween(Carbon::create($currentYear, 5, 1)->startOfDay(), Carbon::create($currentYear, 6, 30)->endOfDay())) {
            $semesterDetails = [
                'type' => 3,
                'start_date' => Carbon::create($currentYear, 5, 1)->startOfDay(),
                'end_date' => Carbon::create($currentYear, 6, 30)->endOfDay(),
            ];
        }

        // If $semesterDetails is still null, it means the current date is not a semester start period.
        if (!$semesterDetails) {
            $this->error('The current date does not fall within a defined academic semester for new registrations. Exiting.');
            Log::warning('CreateNewUserSemesters: No defined semester period for current date.', ['date' => now()->toDateString()]);
            return Command::FAILURE; // The command exits here if it's not a semester start month
        }


        $students = User::where('roleID', 1)->get();

        $countCreated = 0;
        $countSkipped = 0;

        foreach ($students as $user) {
            DB::beginTransaction();
            try {
             
                $latestUserSemester = $user->userSemesters()->orderBy('semester_number', 'desc')->first();
                $nextSemesterNumber = ($latestUserSemester ? $latestUserSemester->semester_number : 0) + 1;


                $existingUserSemesterForPeriod = UserSemester::where('userID', $user->id)
                                                            ->whereDate('start_date', $semesterDetails['start_date'])
                                                            ->whereDate('end_date', $semesterDetails['end_date'])
                                                            ->first();

                if ($existingUserSemesterForPeriod) {
                    $this->comment("Skipping user {$user->id}: UserSemester for {$semesterDetails['start_date']->format('Y-m-d')} to {$semesterDetails['end_date']->format('Y-m-d')} already exists.");
                    $countSkipped++;
                    DB::rollBack(); 
                    continue; 
                }

      
                $userSpecializationID = $user->userSemesters()->orderBy('id', 'desc')->first()->SpecializationID ?? null;

                UserSemester::create([
                    'userID' => $user->id,
                    'SpecializationID' => $userSpecializationID, 
                    'start_date' => $semesterDetails['start_date'],
                    'end_date' => $semesterDetails['end_date'],
                    'semester_number' => $nextSemesterNumber,
                    'semester_hours' => 0, 
                    'year_degree' => 0,   
                    'has_registered_subjects' => false, 
                ]);

                $this->info("Created UserSemester for user ID: {$user->id}, Semester Number: {$nextSemesterNumber} (Type: {$semesterDetails['type']})");
                $countCreated++;
                DB::commit();

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Failed to create UserSemester for user ID: {$user->id}. Error: " . $e->getMessage());
                $this->error("Failed to create UserSemester for user ID: {$user->id}. Check logs.");
            }
        }

        $this->info("Command finished. Total UserSemesters created: {$countCreated}. Skipped: {$countSkipped}.");
        Log::info('Finished CreateNewUserSemesters command.', ['created' => $countCreated, 'skipped' => $countSkipped]);

        return Command::SUCCESS;
    }
}
