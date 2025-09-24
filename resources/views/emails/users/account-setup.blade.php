<x-mail::message>
# Welcome to {{ config('app.name') }}, {{ $user->firstname }}!

Your account has been created. Please click the button below to set up your password and verify your email address.

<x-mail::button :url="$setupUrl">
Set Up Your Account
</x-mail::button>

This link will expire in 24 hours for security reasons. After setting your password, your email will be automatically verified.

If you did not request this account, please ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>