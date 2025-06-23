<x-mail::message>
# Welcome to ASPU HUB!

Hello {{ $user->first_name }},

Congratulations! Your application to become a supervisor has been approved.

Your account has been created, and you can now log in to the supervisor control panel using the following credentials:

**Email:** {{ $user->email }}
**Password:** `{{ $password }}`

For security reasons, we strongly recommend that you change your password immediately after your first login.

<x-mail::button :url="route('admin.login')">
Login Now
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
