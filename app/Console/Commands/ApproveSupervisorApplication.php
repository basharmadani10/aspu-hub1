<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RequestJob;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\SupervisorCredentialsMail;

class ApproveSupervisorApplication extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:approve-supervisor {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Approves a pending supervisor application and sends credentials.';


    public function handle()
    {
        $email = $this->argument('email');
        $this->info("Attempting to approve application for: {$email}");

        $application = RequestJob::where('email', $email)->where('is_accepted', false)->first();

        if (!$application) {
            $this->error("No pending application found for the email: {$email}");
            return 1;
        }

        if (!$this->confirm("Are you sure you want to approve the application for {$application->first_name} {$application->last_name}?")) {
            $this->warn('Approval cancelled by user.');
            return;
        }

        try {
            $password = Str::random(10);
            $this->info('Generated a secure random password.');

            $user = User::create([
                'first_name' => $application->first_name,
                'last_name'  => $application->last_name,
                'email'      => $application->email,
                'password'   => Hash::make($password),
                'roleID'     => 2, // roleID for Supervisor
                'is_approved'=> true,
            ]);
            $this->info("User account created successfully for {$user->email}.");

            Mail::to($user->email)->send(new SupervisorCredentialsMail($user, $password));
            $this->info("Credentials email sent to {$user->email}.");

            $application->update(['is_accepted' => true]);
            $this->info('Application status marked as approved.');

        } catch (\Exception $e) {
            $this->error('An error occurred during the approval process:');
            $this->error($e->getMessage());
            return 1;
        }

        $this->info('====================================================');
        $this->info('Supervisor application approved successfully!');
        $this->info("Email: {$user->email}");
        $this->info("Password: {$password}");
        $this->info('====================================================');

        return 0;
    }
}
