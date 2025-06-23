<?php

namespace App\Observers;

use App\Models\RequestJob;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\SupervisorCredentialsMail; // Ensure this Mailable exists and is correctly configured
use Illuminate\Support\Facades\Log;

class RequestJobObserver
{
    /**
     * Handle the RequestJob "updated" event.
     *
     * @param  \App\Models\RequestJob  $requestJob
     * @return void
     */
    public function updated(RequestJob $requestJob): void
    {
        Log::info('RequestJobObserver running for ID: ' . $requestJob->id);

        // Check if 'is_accepted' was changed and if its new value is true
        if ($requestJob->wasChanged('is_accepted') && $requestJob->is_accepted) {
            Log::info('is_accepted was changed to true, proceeding to check/create user and send email.');

            $email = $requestJob->email;
            $password = Str::random(10); // Generate a temporary password

            // --- IMPORTANT FIX: Check if a user with this email already exists ---
            $user = User::where('email', $email)->first();

            if ($user) {
                // Scenario 1: User already exists.
                // You likely want to update their role to supervisor if it's not already.
                // Avoid creating a new password for an existing user unless explicitly required.
                Log::info('User with email ' . $email . ' already exists. Updating role if necessary.');
                if ($user->roleID != 2) { // Assuming 2 is the roleID for Supervisor
                    $user->roleID = 2;
                    $user->is_approved = true; // Set to true if your User model has this
                    $user->save();
                    Log::info('User role updated to Supervisor for ' . $email);
                }

                // Send a different kind of email for existing users (e.g., "Your status is now active")
                // Or you can still send credentials, but ensure your Mailable handles this gracefully
                // by not displaying a new password if $password is null.
                Mail::to($email)->send(new SupervisorCredentialsMail($user, null)); // Pass null for password for existing user
                Log::info('Email sent to existing user: ' . $email);

            } else {
                // Scenario 2: User does not exist, create a new one.
                Log::info('No existing user found for ' . $email . '. Creating new user.');
                try {
                    $newUser = User::create([
                        'first_name' => $requestJob->first_name,
                        'last_name'  => $requestJob->last_name,
                        'email'      => $email,
                        'password'   => Hash::make($password), // Hash the generated password
                        'roleID'     => 2, // Supervisor role
                        'is_approved'=> true, // Set to true for newly approved supervisors
                    ]);

                    // Send the welcome email with the newly generated credentials
                    Mail::to($newUser->email)->send(new SupervisorCredentialsMail($newUser, $password)); // Send plain password for initial login
                    Log::info('New user created and email sent to: ' . $newUser->email);

                } catch (\Illuminate\Database\QueryException $e) {
                    // This catch block is a fallback, but the `User::where()->first()` check
                    // should prevent most duplicate entry errors.
                    Log::error('Database error while creating supervisor user for ' . $email . ': ' . $e->getMessage());
                    // You might consider rolling back the is_accepted status or notifying an admin here
                }
            }
        }
    }
}
